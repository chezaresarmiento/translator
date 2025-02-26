<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class TranslationProcessed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public $userId;
    public $timestamp;
    public $originalText;
    public $translatedText;

    /**
     * Create a new event instance.
     */
    public function __construct($userId, $timestamp, $originalText, $translatedText)
    {
        $this->userId = $userId;
        $this->timestamp = $timestamp;
        $this->originalText = $originalText;
        $this->translatedText = $translatedText;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        return new PrivateChannel('translations.' . $this->userId);
    }

    /**
     * Define the event's broadcast data.
     */
    public function broadcastWith()
    {
        return [
            'timestamp' => $this->timestamp,
            'originalText' => $this->originalText,
            'translatedText' => $this->translatedText,
        ];
    }
}
