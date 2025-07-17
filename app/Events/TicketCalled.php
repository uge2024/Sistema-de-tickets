<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketCalled
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $areaId;
    public $ticketNumber;
    public $areaName;
    public $puestoName;
    public $timestamp;
    public $isRecall;

    public function __construct($areaId, $ticketNumber, $areaName = null, $puestoName = null, $isRecall = false)
    {
        $this->areaId = $areaId;
        $this->ticketNumber = $ticketNumber;
        $this->areaName = $areaName;
        $this->puestoName = $puestoName;
        $this->isRecall = $isRecall;
        $this->timestamp = now()->toISOString();
        
        \Log::info("🎯 EVENTO CREADO: TicketCalled", [
            'areaId' => $areaId,
            'ticketNumber' => $ticketNumber,
            'isRecall' => $isRecall
        ]);
    }

    // 🔥 CANAL PÚBLICO - Todas las pantallas escuchan
    public function broadcastOn()
    {
        return new Channel('display-screen');
    }

    // 🔥 NOMBRE DEL EVENTO
    public function broadcastAs()
    {
        return 'ticket.called';
    }

    // 🔥 DATOS QUE SE ENVÍAN
    public function broadcastWith()
    {
        return [
            'areaId' => $this->areaId,
            'ticketNumber' => $this->ticketNumber,
            'areaName' => $this->areaName,
            'puestoName' => $this->puestoName,
            'isRecall' => $this->isRecall,
            'timestamp' => $this->timestamp,
        ];
    }
}
