<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Dashboard extends Component
{
    public $puesto;
    public $area;
    
    // Estadísticas del usuario actual
    public $myStats = [];
    
    // Filtros
    public $selectedPeriod = 'today';
    public $availablePeriods = [
        'today' => 'Hoy',
        'week' => 'Esta Semana',
        'month' => 'Este Mes',
        'year' => 'Este Año'
    ];

    public function mount()
    {
        $this->puesto = Auth::user()->puesto;
        $this->area = $this->puesto?->area;
        $this->loadMyStats();
    }

    public function updatedSelectedPeriod()
    {
        $this->loadMyStats();
    }

    private function loadMyStats()
    {
        $userId = Auth::id();
        
        // Total de tickets atendidos
        $totalTickets = $this->getTicketCount($userId);
        
        // Tickets por tipo
        $ticketsPorTipo = $this->getTicketsByType($userId);
        
        // Calcular porcentajes
        $porcentajeNormal = $totalTickets > 0 ? round(($ticketsPorTipo['normal'] / $totalTickets) * 100, 1) : 0;
        $porcentajeSenior = $totalTickets > 0 ? round(($ticketsPorTipo['senior'] / $totalTickets) * 100, 1) : 0;
        
        $this->myStats = [
            'total' => $totalTickets,
            'normal' => $ticketsPorTipo['normal'],
            'senior' => $ticketsPorTipo['senior'],
            'porcentaje_normal' => $porcentajeNormal,
            'porcentaje_senior' => $porcentajeSenior
        ];
    }
    
    private function getTicketCount($userId)
    {
        // Usar el método más confiable para cada período
        return match($this->selectedPeriod) {
            'today' => $this->getTodayTickets($userId),
            'week' => $this->getWeekTickets($userId),
            'month' => $this->getMonthTickets($userId),
            'year' => $this->getYearTickets($userId),
            default => $this->getTodayTickets($userId)
        };
    }
    
    private function getTicketsByType($userId)
    {
        // Construir la consulta base
        $baseQuery = Ticket::where('user_id', $userId)->where('status', 'called');
        
        // Aplicar filtro de fecha según el período
        $query = match($this->selectedPeriod) {
            'today' => $baseQuery->whereDate('updated_at', Carbon::today()),
            'week' => $baseQuery->whereBetween('updated_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ]),
            'month' => $baseQuery->whereYear('updated_at', Carbon::now()->year)
                                ->whereMonth('updated_at', Carbon::now()->month),
            'year' => $baseQuery->whereYear('updated_at', Carbon::now()->year),
            default => $baseQuery->whereDate('updated_at', Carbon::today())
        };
        
        // Obtener conteo por tipo
        $tipos = $query->select('type', DB::raw('COUNT(*) as total'))
                      ->groupBy('type')
                      ->pluck('total', 'type')
                      ->toArray();
        
        return [
            'normal' => $tipos['normal'] ?? 0,
            'senior' => $tipos['senior'] ?? 0
        ];
    }
    
    private function getTodayTickets($userId)
    {
        return Ticket::where('user_id', $userId)
            ->where('status', 'called')
            ->whereDate('updated_at', Carbon::today())
            ->count();
    }
    
    private function getWeekTickets($userId)
    {
        return Ticket::where('user_id', $userId)
            ->where('status', 'called')
            ->whereBetween('updated_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])
            ->count();
    }
    
    private function getMonthTickets($userId)
    {
        return Ticket::where('user_id', $userId)
            ->where('status', 'called')
            ->whereYear('updated_at', Carbon::now()->year)
            ->whereMonth('updated_at', Carbon::now()->month)
            ->count();
    }
    
    private function getYearTickets($userId)
    {
        return Ticket::where('user_id', $userId)
            ->where('status', 'called')
            ->whereYear('updated_at', Carbon::now()->year)
            ->count();
    }

    public function refreshStats()
    {
        $this->loadMyStats();
        $this->dispatch('stats-refreshed');
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}