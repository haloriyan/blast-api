<?php

namespace App\Jobs;

use App\Models\Broadcast;
use App\Models\BroadcastLog;
use App\Models\UserNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Coeg implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $broadcast;
    public $destination;
    public $device;
    public $message;

    /**
     * Create a new job instance.
     */
    public function __construct($props)
    {
        $this->broadcast = $props['broadcast'];
        $this->destination = $props['destination'];
        $this->device = $props['device'];
        $this->message = $props['message'];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        sleep($this->broadcast->delay_time / 1000);

        // Saving log
        BroadcastLog::create([
            'broadcast_id' => $this->broadcast->id,
            'contact_id' => $this->destination->id,
            'status' => "SENT"
        ]);

        // Requesting to whatsapp
        $reqPayload = [
            'clientId' => $this->device->client_id,
            'number' => $this->destination->country_code.$this->destination->whatsapp,
            'message' => $this->message,
        ];
        Http::post('http://127.0.0.1:2024/send-message', $reqPayload);

        // SEND NOTIF IS DONE
        $logs = BroadcastLog::where('broadcast_id', $this->broadcast->id)->get(['id']);
        
        if ($logs->count() >= $this->broadcast->group_member) {
            UserNotification::create([
                'user_id' => $this->broadcast->user_id,
                'body' => "Pengiriman selesai",
                'has_read' => false,
                'action' => "/history"
            ]);

            Broadcast::where('id', $this->broadcast->id)->update([
                'delivery_status' => "DONE",
            ]);
        }
    }
}
