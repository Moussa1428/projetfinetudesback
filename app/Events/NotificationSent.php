<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationSent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $notification;

    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }
    public function broadcastOn()
    {
        // Canal générique pour push front
        return new PrivateChannel('notifications.' . $this->notification->target_type . '.' . $this->notification->target_id);
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->notification->message,
            'sender_id' => $this->notification->sender_id,
            'target_type' => $this->notification->target_type,
            'target_id' => $this->notification->target_id,
        ];
    }
}
