<?php

namespace App\Jobs;

use FFMPEG, Storage;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\Upload;

use App\Services\ExtractMediaMetadata as MediaService;

class ExtractMediaMetadata implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $uploadId;

    public function __construct($uploadId)
    {
        $this->uploadId = $uploadId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $upload = Upload::find($this->uploadId);
        if ($upload) {
            (new MediaService)->extract($upload);
        }
    }
}
