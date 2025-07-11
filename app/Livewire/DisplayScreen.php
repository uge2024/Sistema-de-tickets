<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Area;
use App\Models\Video;

class DisplayScreen extends Component
{
    public $areas;
    public $videoUrls = [];
    public $lastUpdatedTickets = [];
    public $blinkingAreas = [];
    public $isInitialized = false;

    protected $listeners = ['ticket-updated' => 'updateTicket'];

    public function mount()
    {
        $this->initializeData();
        $this->loadVideos();
        // Marcar como inicializado DESPUÉS de cargar los datos iniciales
        $this->isInitialized = true;
    }

    public function initializeData()
    {
        $this->areas = Area::with(['display.ticket', 'display.puesto'])->get();
        
        // Inicializar arrays sin activar parpadeos
        foreach ($this->areas as $area) {
            $this->blinkingAreas[$area->id] = false;
            $this->lastUpdatedTickets[$area->id] = $area->display?->ticket?->ticket_number ?? null;
        }
    }

    public function loadAreas()
    {
        $this->areas = Area::with(['display.ticket', 'display.puesto'])->get();

        foreach ($this->areas as $area) {
            // Inicializar si no existe la clave
            if (!array_key_exists($area->id, $this->blinkingAreas)) {
                $this->blinkingAreas[$area->id] = false;
            }
            
            $currentTicket = $area->display?->ticket?->ticket_number ?? null;
            $lastTicket = $this->lastUpdatedTickets[$area->id] ?? null;
            
            // Detectar cambios significativos
            $hasChanged = $this->hasTicketChanged($currentTicket, $lastTicket);
            
            if ($this->isInitialized && $hasChanged) {
                $this->activateBlinking($area->id, $currentTicket);
            }
            
            // Actualizar el último ticket conocido
            $this->lastUpdatedTickets[$area->id] = $currentTicket;
        }
    }

    private function hasTicketChanged($currentTicket, $lastTicket)
    {
        // No hay cambio si ambos son null
        if ($currentTicket === null && $lastTicket === null) {
            return false;
        }
        
        // Hay cambio si son diferentes
        return $currentTicket !== $lastTicket;
    }

    private function activateBlinking($areaId, $ticketNumber)
{
    $this->blinkingAreas[$areaId] = true;
    
    // Emitir eventos
    $this->dispatch('play-notification-sound');
    
    // 🔥 CAMBIO: Usar evento diferente que NO cause bucle
    $this->dispatch('blink-start', [
        'areaId' => $areaId,
        'ticketNumber' => $ticketNumber,
        'timestamp' => microtime(true)
    ]);
    
    // Log para debugging
    \Log::info("Activando parpadeo para área {$areaId} con ticket: {$ticketNumber}");
}

    public function loadVideos()
    {
        $this->videoUrls = Video::where('is_active', true)
            ->get()
            ->map(fn ($video) => ['url' => $video->url])
            ->toArray();
    }

    public function updateTicket($data)
{
    // Validar datos recibidos
    if (!isset($data['areaId']) || !isset($data['ticketNumber'])) {
        \Log::warning('Datos incompletos en updateTicket', $data);
        return;
    }

    $areaId = $data['areaId'];
    $ticketNumber = $data['ticketNumber'];
    
    // 🔥 SIEMPRE activar parpadeo (sin verificar cambios)
    $this->blinkingAreas[$areaId] = true;
    $this->lastUpdatedTickets[$areaId] = $ticketNumber;
    
    // 🔥 NO llamar activateBlinking aquí (evitar bucle)
    // 🔥 NO llamar loadAreas aquí (evitar bucle)
    
    // Solo emitir el evento directo
    $this->dispatch('blink-start', [
        'areaId' => $areaId,
        'ticketNumber' => $ticketNumber,
        'timestamp' => microtime(true)
    ]);
    
    \Log::info("✅ UpdateTicket procesado para área {$areaId}, ticket: {$ticketNumber}");
}

    public function stopBlink($areaId)
    {
        if (array_key_exists($areaId, $this->blinkingAreas)) {
            $this->blinkingAreas[$areaId] = false;
            \Log::info("Parpadeo detenido para área: {$areaId}");
        }
    }

    public function render()
    {
        return view('livewire.display-screen');
    }
}