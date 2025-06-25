<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Area;
use App\Models\Ticket;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;

class GenerateTicket extends Component
{
    public $areas;
    public $selectedArea = null;
    public $type = 'normal';
    public $showModal = false;

    public function mount()
    {
        $this->areas = Area::all();
    }

    // Agregar el listener para el evento 'area-created'
    public function getListeners()
    {
        return [
            'area-created' => 'refreshAreas',
        ];
    }

    // Método para actualizar la lista de áreas
    public function refreshAreas()
    {
        $this->areas = Area::all();
        \Log::info('Áreas actualizadas en GenerateTicket: ' . $this->areas->count() . ' áreas cargadas.');
    }
    
    public function openModalForArea($areaId)
    {
        $this->selectedArea = $areaId;
        $this->showModal = true;
    }

    public function generateTicketWithType($type)
    {
        $this->type = $type;

        $this->validate([
            'selectedArea' => 'required|exists:areas,id',
            'type' => 'required|in:normal,senior',
        ]);

        // Obtener el área
        $area = Area::find($this->selectedArea);
        if (!$area) {
            \Log::error('Área no encontrada para ID: ' . $this->selectedArea);
            session()->flash('error', 'El área seleccionada no existe.');
            $this->showModal = false;
            return;
        }

        $query = Ticket::where('area_id', $this->selectedArea)
            ->whereDate('created_at', today());

        // Determinar el último número según el tipo
        $lastTicket = $query->where('type', $type)->orderBy('ticket_number', 'desc')->first();

        $number = $lastTicket
        ? (int)preg_replace('/[^0-9]/', '', $lastTicket->ticket_number) + 1
        : 1;

        $ticketNumber = $type === 'normal'
        ? sprintf('%s%03d', $area->code, $number)
        : sprintf('S%03d', $number); // "S" para Tercera Edad

        // Crear el ticket
        $ticket = Ticket::create([
            'area_id' => $this->selectedArea,
            'ticket_number' => $ticketNumber,
            'type' => $this->type,
            'status' => 'waiting',
        ]);

        if (!$ticket) {
            \Log::error('Fallo al crear el ticket para área ID: ' . $this->selectedArea);
            session()->flash('error', 'No se pudo crear la ficha.');
            $this->showModal = false;
            return;
        }

        // Imprimir la ficha
        try {
            $connector = new FilePrintConnector("/dev/usb/lp2"); // Ajusta el path según tu dispositivo
            $printer = new Printer($connector);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("GOBERNACIÓN DE COCHABAMBA\n");
            $printer->text("Unidad de Recaudaciones y Tributación\n");
            $printer->setTextSize(2, 2); // Tamaño grande para el número
            $printer->text("{$ticket->ticket_number}\n");
            $printer->setTextSize(1, 1); // Volver a tamaño normal
            $printer->text("Área: {$area->name}\n"); // Añadir el área
            $printer->text("No arrójelo papel en la vía pública, de esta manera\n");
            $printer->text("contribuye con el cuidado del medio ambiente.\n");
            $printer->text(now()->format('d-m-Y H:i') . "\n");
            $printer->text("UGE\n");
            $printer->text("------------------------\n");
            $printer->cut();
            $printer->close();
            \Log::info('Ticket printed successfully: ' . $ticket->ticket_number);
            session()->flash('success', 'Ficha generada e impresa correctamente: ' . $ticket->ticket_number);
        } catch (\Exception $e) {
            \Log::error('Failed to print ticket: ' . $e->getMessage());
            session()->flash('error', 'Ficha generada, pero no se pudo imprimir (' . $ticketNumber . '): ' . $e->getMessage());
        }

        $this->dispatch('ticket-generated', ['areaId' => $this->selectedArea, 'ticketNumber' => $ticketNumber]);
        $this->selectedArea = null;
        $this->type = 'normal';
        $this->showModal = false;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedArea = null;
        $this->type = 'normal';
    }
    
    public function render()
    {
        return view('livewire.generate-ticket');
    }
}
