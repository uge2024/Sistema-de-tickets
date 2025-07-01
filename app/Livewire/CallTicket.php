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
    public $area; // ← CAMBIO: Solo una área en lugar de colección
    public $pendingTickets = [];
    public $calledTickets = [];

    public function mount()
    {
        $this->puesto = Auth::user()->puesto;
        if (!$this->puesto) {
            abort(403, 'No tienes un puesto asignado');
        }
        
        // ← CAMBIO: Cargar directamente el área del puesto
        $this->area = $this->puesto->area;
        if (!$this->area) {
            abort(403, 'Tu puesto no tiene un área asignada');
        }
        
        $this->loadTickets();
    }

    public function loadTickets()
    {
        // ← CAMBIO: Trabajar directamente con el área, no con colección
        $this->pendingTickets = Ticket::with('area')
            ->where('area_id', $this->area->id)
            ->where('status', 'waiting')
            ->orderByRaw("type = 'senior' DESC")
            ->orderBy('created_at')
            ->get();

        $this->calledTickets = Ticket::with(['area', 'user'])
            ->where('area_id', $this->area->id)
            ->where('status', 'called')
            ->where('user_id', Auth::id())
            ->orderByDesc('updated_at')
            ->take(5)
            ->get();
    }

    public function callNextTicket($areaId)
    {
        // Verificar que el área corresponde al puesto del usuario
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
            // Actualizar ticket con el usuario que lo llama
            $nextTicket->update([
                'status' => 'called', 
                'user_id' => Auth::id(),
                'updated_at' => now()
            ]);

            // Actualizar display con puesto y área
            Display::updateOrCreate(
                ['area_id' => $areaId],
                [
                    'ticket_id' => $nextTicket->id, 
                    'puesto_id' => $this->puesto->id,
                    'called_at' => now()
                ]
            );

            // Recargar tickets
            $this->loadTickets();
            
            // Eventos
            $this->dispatch('ticket-called', areaId: $areaId);
            $this->dispatch('ticket-updated', [
                'areaId' => $areaId,
                'ticketNumber' => $nextTicket->ticket_number,
                'areaName' => $this->area->name,
                'puestoName' => $this->puesto->name,
            ])->to('display-screen');
            $this->dispatch('refresh-view');
            
            \Log::info("Ticket {$nextTicket->ticket_number} llamado por usuario " . Auth::id() . " desde puesto {$this->puesto->name} en área {$this->area->name}");
        } else {
            $this->dispatch('no-tickets', areaId: $areaId);
        }
    }

    public function recallTicket($ticketId)
    {
        $ticket = Ticket::with('area')->findOrFail($ticketId);

        // Verificar que el ticket fue llamado por el usuario actual
        if ($ticket->status === 'called' && 
            $ticket->area_id === $this->puesto->area_id && 
            $ticket->user_id === Auth::id()) {
            
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
            
            $this->dispatch('ticket-called', ['areaId' => $ticket->area_id]);
            
            $this->dispatch('ticket-updated', [
                'areaId' => $ticket->area_id,
                'ticketNumber' => $ticket->ticket_number,
                'areaName' => $ticket->area->name,
            ])->to('display-screen');
            
            $this->dispatch('refresh-view');
            
            \Log::info("Ticket {$ticket->ticket_number} vuelto a llamar por usuario " . Auth::id() . " en puesto {$this->puesto->name}");
        }
    }

    public function render()
    {
        return view('livewire.call-ticket');
    }
}