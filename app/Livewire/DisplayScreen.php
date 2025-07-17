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
    
    // 🔥 NUEVO: Cache para evitar consultas innecesarias
    public $lastLoadTime = 0;
    public $areasHash = '';
    
    // 🔥 NUEVO: Debounce para loadAreas
    public $lastLoadCall = 0;
    private const LOAD_DEBOUNCE = 3; // 3 segundos mínimo entre llamadas

    protected $listeners = ['ticket-updated' => 'updateTicket'];

    public function mount()
    {
        $this->initializeData();
        $this->loadVideos();
        $this->isInitialized = true;
    }

    public function initializeData()
    {
        $this->areas = Area::with(['display.ticket', 'display.puesto'])->get();
        
        // 🔥 NUEVO: Generar hash inicial
        $this->areasHash = $this->generateAreasHash();
        
        foreach ($this->areas as $area) {
            $this->blinkingAreas[$area->id] = false;
            $this->lastUpdatedTickets[$area->id] = $area->display?->ticket?->ticket_number ?? null;
        }
    }

    // 🔥 NUEVO: Método optimizado con debounce y cache
    public function loadAreas()
    {
        $currentTime = microtime(true);
        
        // Debounce: evitar llamadas muy frecuentes
        if ($currentTime - $this->lastLoadCall < self::LOAD_DEBOUNCE) {
            \Log::info("LoadAreas bloqueado por debounce");
            return;
        }
        
        $this->lastLoadCall = $currentTime;
        
        // Cargar datos
        $newAreas = Area::with(['display.ticket', 'display.puesto'])->get();
        $newHash = $this->generateAreasHash($newAreas);
        
        // 🔥 OPTIMIZACIÓN: Solo procesar si hay cambios reales
        if ($newHash === $this->areasHash && $this->isInitialized) {
            \Log::info("Sin cambios detectados, saltando procesamiento");
            return;
        }
        
        \Log::info("Cambios detectados, procesando áreas");
        $this->areasHash = $newHash;
        $this->areas = $newAreas;

        foreach ($this->areas as $area) {
            if (!array_key_exists($area->id, $this->blinkingAreas)) {
                $this->blinkingAreas[$area->id] = false;
            }
            
            $currentTicket = $area->display?->ticket?->ticket_number ?? null;
            $lastTicket = $this->lastUpdatedTickets[$area->id] ?? null;
            
            $hasChanged = $this->hasTicketChanged($currentTicket, $lastTicket);
            
            if ($this->isInitialized && $hasChanged) {
                $this->activateBlinking($area->id, $currentTicket);
            }
            
            $this->lastUpdatedTickets[$area->id] = $currentTicket;
        }
        
        $this->lastLoadTime = $currentTime;
    }

    // 🔥 NUEVO: Generar hash para detectar cambios reales
    private function generateAreasHash($areas = null)
    {
        $areas = $areas ?? $this->areas;
        if (!$areas) return '';
        
        $data = [];
        foreach ($areas as $area) {
            $data[$area->id] = [
                'ticket' => $area->display?->ticket?->ticket_number ?? null,
                'called_at' => $area->display?->called_at ?? null,
                'puesto' => $area->display?->puesto?->name ?? null,
            ];
        }
        
        return md5(json_encode($data));
    }

    private function hasTicketChanged($currentTicket, $lastTicket)
    {
        if ($currentTicket === null && $lastTicket === null) {
            return false;
        }
        return $currentTicket !== $lastTicket;
    }

    private function activateBlinking($areaId, $ticketNumber)
    {
        $this->blinkingAreas[$areaId] = true;
        
        // 🔥 OPTIMIZACIÓN: Solo un evento, sin loops
        $this->dispatch('blink-start', [
            'areaId' => $areaId,
            'ticketNumber' => $ticketNumber,
            'timestamp' => microtime(true)
        ]);
        
        // 🔥 REDUCIR LOGS: Solo log importante
        \Log::info("Parpadeo activado: área {$areaId}, ticket: {$ticketNumber}");
    }

    public function loadVideos()
    {
        // 🔥 OPTIMIZACIÓN: Cache de videos si no han cambiado
        static $lastVideoCheck = 0;
        static $cachedVideos = null;
        
        $currentTime = time();
        
        if ($cachedVideos !== null && ($currentTime - $lastVideoCheck) < 300) { // 5 minutos cache
            $this->videoUrls = $cachedVideos;
            return;
        }
        
        $this->videoUrls = Video::where('is_active', true)
            ->get()
            ->map(fn ($video) => ['url' => $video->url])
            ->toArray();
            
        $cachedVideos = $this->videoUrls;
        $lastVideoCheck = $currentTime;
    }

    public function updateTicket($data)
    {
        if (!isset($data['areaId']) || !isset($data['ticketNumber'])) {
            \Log::warning('Datos incompletos en updateTicket', $data);
            return;
        }

        $areaId = $data['areaId'];
        $ticketNumber = $data['ticketNumber'];
        
        // 🔥 OPTIMIZACIÓN: Solo actualizar estado, NO recargar todo
        $this->blinkingAreas[$areaId] = true;
        $this->lastUpdatedTickets[$areaId] = $ticketNumber;
        
        // Solo emitir evento directo
        $this->dispatch('blink-start', [
            'areaId' => $areaId,
            'ticketNumber' => $ticketNumber,
            'timestamp' => microtime(true)
        ]);
        
        \Log::info("UpdateTicket procesado: área {$areaId}, ticket: {$ticketNumber}");
    }

    public function stopBlink($areaId)
    {
        if (array_key_exists($areaId, $this->blinkingAreas)) {
            $this->blinkingAreas[$areaId] = false;
            \Log::info("Parpadeo detenido: área {$areaId}");
        }
    }

    // 🔥 NUEVO: Método para polling inteligente
    public function checkForUpdates()
    {
        // Solo hacer la verificación real si han pasado suficientes segundos
        $currentTime = microtime(true);
        if ($currentTime - $this->lastLoadTime < 8) { // Mínimo 8 segundos
            return;
        }
        
        $this->loadAreas();
    }

    public function render()
    {
        return view('livewire.display-screen');
    }
}