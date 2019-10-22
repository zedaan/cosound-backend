<?php

namespace App\Jobs;

use Storage;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\Upload;

class DeletePost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $postId;

    public function __construct($postId)
    {
        $this->postId = $postId; 
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $medias = Upload::whereUploadableId($this->postId)->whereUploadableType('App\Models\Post')->get();

        $files_to_delete = [];

        foreach($medias as $media) {
            
            array_push($files_to_delete, $media->getOriginal('path'));

            if ($media->file_type === "audio") {
                
                $file = $media->metadata->albumart ?? null;
                $file = str_replace(env('AWS_URL'), "", $file);
                array_push($files_to_delete, $file);

            } else if ($media->file_type === "video") {

                $file = $media->metadata->thumbnail ?? null;
                $file = str_replace(env('AWS_URL'), "", $file);
                array_push($files_to_delete, $file);

            } else if ($media->file_type === "image") {

                $file = $media->metadata->thumbnail_small ?? null;
                $file = str_replace(env('AWS_URL'), "", $file);
                array_push($files_to_delete, $file);

                $file = $media->metadata->thumbnail_normal ?? null;
                $file = str_replace(env('AWS_URL'), "", $file);
                array_push($files_to_delete, $file);
            }
        }

        $files_to_delete = array_filter($files_to_delete);

        foreach($files_to_delete as $file) {
            Storage::delete($file);
        }
        
        Upload::whereUploadableId($this->postId)->whereUploadableType('App\Models\Post')->delete();
    }
}
