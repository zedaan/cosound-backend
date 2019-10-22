<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Services\GetStreamService;

class Notification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $userId, $data, $action;

    public function __construct($userId, $data, $action)
    {
        $this->userId = $userId;
        $this->data = $data;
        $this->action = $action;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $getStreamService = new GetStreamService;
        $this->action === "send"
            ? $getStreamService->sendNotification($this->userId, $this->data)
            : $getStreamService->removeNotification($this->userId, $this->data);
    }
}
