<?php

namespace App\Jobs;

use Storage;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\User;

class ExtractThumbnail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::find($this->userId);
        if (! $user) {
            return true;
        }

        $avatar = $user->getOriginal('avatar');
        $now = time();
        
        $thumbnail_file = storage_path().'/app/thumbnails/avatar_thumb_' . $this->userId . '-' . $now . '.png';
        
        $command = env('FFMPEG_PATH') . ' -i ' . $user->avatar . ' -vf "scale=\'min(200, iw)\':-1" ' . $thumbnail_file . ' 2>&1';

        $exec = exec($command, $output, $return);
        if ($return == 0) {

            $thumbnail_file = 'thumbnails/avatar_thumb_' . $this->userId . '-' . $now . '.png';

            $content = Storage::disk('local')->get($thumbnail_file);

            Storage::put($thumbnail_file, $content, 'public');
            Storage::disk('local')->delete($thumbnail_file);

            $user->thumbnail = $thumbnail_file;
            $user->save();
        }
    }
}
