<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Area;
use App\Models\Ticket;
use App\Models\Display;
use Illuminate\Support\Facades\Auth;

class CallTicket extends Component
{
    public $puesto;
    public $areas;
    public $pendingTickets = [];
    public $calledTickets = [];

    public function mount()
    {
        $this->puesto = Auth::user()->puesto;
        if (!$this->puesto) {
            abort(403, 'No tienes un puesto asignado');
        }
        $this->areas = collect([$this->puesto->area]);
        $this->loadTickets();
    }

    public function loadTickets()
    {
        $this->pendingTickets = $this->areas->flatMap(function ($area) {
            return Ticket::with('area')
                ->where('area_id', $area->id)
                ->where('status', 'waiting')
                ->orderByRaw("type = 'senior' DESC")
                ->orderBy('created_at')
                ->get();
        })->groupBy('area_id');

        $this->calledTickets = $this->areas->flatMap(function ($area) {
            return Ticket::with('area')
                ->where('area_id', $area->id)
                ->where('status', 'called')
                ->orderByDesc('updated_at')
                ->take(5)
                ->get();
        })->groupBy('area_id');
    }

    public function callNextTicket($areaId)
    {
        if ($this->puesto->area_id !== $areaId) {
            abort(403, 'No autorizado para esta área');
        }

        $nextTicket = Ticket::with('area')
            ->where('area_id', $areaId)
            ->where('status', 'waiting')
            ->orderByRaw("type = 'senior' DESC")
            ->orderBy('created_at')
            ->first();

        if ($nextTicket) {
            $nextTicket->update(['status' => 'called', 'updated_at' => now()]);

            // ← AQUÍ ESTÁ EL CAMBIO: Agregar puesto_id
            Display::updateOrCreate(
                ['area_id' => $areaId],
                [
                    'ticket_id' => $nextTicket->id, 
                    'puesto_id' => $this->puesto->id,  // ← LÍNEA AGREGADA
                    'called_at' => now()
                ]
            );

            // Forzar recarga inmediata de tickets
            $this->loadTickets();
            $area = $nextTicket->area;
            $this->dispatch('ticket-called', areaId: $areaId);
            $this->dispatch('ticket-updated', [
                'areaId' => $areaId,
                'ticketNumber' => $nextTicket->ticket_number,
                'areaName' => $area->name,
            ])->to('display-screen');
            $this->dispatch('refresh-view'); // Evento para forzar actualización de la vista
        } else {
            $this->dispatch('no-tickets', areaId: $areaId);
        }
    }

    public function recallTicket($ticketId)
{
    $ticket = Ticket::with('area')->findOrFail($ticketId);

    if ($ticket->status === 'called' && $ticket->area_id === $this->puesto->area_id) {
        // Actualizar timestamp del ticket para marcarlo como recién llamado
        $ticket->update(['updated_at' => now()]);

        // Actualizar/crear display con el puesto actual
        Display::updateOrCreate(
            ['area_id' => $ticket->area_id],
            [
                'ticket_id' => $ticket->id, 
                'puesto_id' => $this->puesto->id,
                'called_at' => now()
            ]
        );

        // Recargar tickets para actualizar la vista
        $this->loadTickets();
        
        // IMPORTANTE: Emitir primero el evento general de ticket-called
        $this->dispatch('ticket-called', ['areaId' => $ticket->area_id]);
        
        // Luego emitir el evento para el display
        $this->dispatch('ticket-updated', [
            'areaId' => $ticket->area_id,
            'ticketNumber' => $ticket->ticket_number,
            'areaName' => $ticket->area->name,
        ])->to('display-screen');
        
        // Finalmente refrescar la vista
        $this->dispatch('refresh-view');
        
        // Log para debugging
        \Log::info("Ticket {$ticket->ticket_number} vuelto a llamar por puesto {$this->puesto->name}");
    }
}

    public function render()
    {
        return view('livewire.call-ticket');
    }
}