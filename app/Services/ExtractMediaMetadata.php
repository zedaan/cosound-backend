<?php

namespace App\Services;

use Carbon\Carbon;
use Config, FFMPEG, Storage;

use App\Models\Upload;
use Illuminate\Support\Str;

class ExtractMediaMetadata
{
    public function extractAudio($upload)
    {
        $meta = (object) $upload->metadata;
        $meta->albumart = null;

        // Extracting tags & duration
        $result = FFMPEG::getMediaInfo($upload->path);

        $meta->title = $result['format']['tags']['title'] ?? null;
        $meta->artist = $result['format']['tags']['artist'] ?? null;
        $meta->album = $result['format']['tags']['album'] ?? null;
        $meta->duration = $result['format']['duration'] ?? null;

        // Extracting albumart
        $albumart_file = storage_path().'/app/albumarts/cover_' . $upload->id . '.png';

        $command = env('FFMPEG_PATH') . ' -i ' . $upload->path . ' ' . $albumart_file . ' 2>&1';

        $exec = exec($command, $output, $return);
        if ($return == 0) {

            $albumart_file = '/albumarts/cover_' . $upload->id . '.png';

            $content = Storage::disk('local')->get($albumart_file);
            Storage::put($albumart_file, $content, 'public');
            Storage::disk('local')->delete($albumart_file);

            $url = env('AWS_URL') . $albumart_file;

            $meta->albumart = $url;
        }
        
        $upload->metadata = $meta;
        $upload->save();
    }

    public function extractVideo($upload)
    {
        $meta = (object) $upload->metadata;
        $meta->thumbnail = null;

        // Fetching mimetype from AWS
        $data = Storage::getMetaData($upload->getOriginal('path'));

        $meta->mimetype = $data['mimetype'];

        $command = env('FFPROBE_PATH') . ' -i ' . $upload->path . ' -show_entries format=duration -v quiet -of csv="p=0"';
        
        $duration = (integer) shell_exec($command);
        $time_to_snap = floor($duration/2);

        // Generating thumbnail
        $thumbnail_file = storage_path().'/app/thumbnails/video_thumb_' . $upload->id . '.png';

        $command = env('FFMPEG_PATH') . ' -ss ' . $time_to_snap .' -i ' . $upload->path . ' -vframes 1 ' . $thumbnail_file . ' 2>&1';

        $exec = exec($command, $output, $return);

        if ($return == 0) {
            $thumbnail_file = '/thumbnails/video_thumb_' . $upload->id . '.png';

            $content = Storage::disk('local')->get($thumbnail_file);
            Storage::put($thumbnail_file, $content, 'public');
            Storage::disk('local')->delete($thumbnail_file);

            $url = env('AWS_URL') . $thumbnail_file;
        
            $meta->thumbnail = $url;
        }

        $upload->metadata = $meta;
        $upload->save();
    }

    public function extractImage($upload)
    {
        $meta = (object) $upload->metadata;
        $meta->thumbnail_small = null;
        $meta->thumbnail_normal = null;

        // Generating thumbnail (small)
        $thumbnail_file = storage_path().'/app/thumbnails/image_thumb_small_' . $upload->id . '.png';
        
        $command = env('FFMPEG_PATH') . ' -i ' . $upload->path . ' -vf "scale=\'min(200, iw)\':-1" ' . $thumbnail_file . ' 2>&1';

        $exec = exec($command, $output, $return);

        if ($return == 0) {
            $thumbnail_file = '/thumbnails/image_thumb_small_' . $upload->id . '.png';

            $content = Storage::disk('local')->get($thumbnail_file);
            Storage::put($thumbnail_file, $content, 'public');
            Storage::disk('local')->delete($thumbnail_file);

            $url = env('AWS_URL') . $thumbnail_file;
        
            $meta->thumbnail_small = $url;
        }

        // Generating thumbnail (normal)
        $thumbnail_file = storage_path().'/app/thumbnails/image_thumb_normal_' . $upload->id . '.png';
        
        $command = env('FFMPEG_PATH') . ' -i ' . $upload->path . ' -vf "scale=\'min(700, iw)\':-1" ' . $thumbnail_file . ' 2>&1';

        $exec = exec($command, $output, $return);

        if ($return == 0) {
            $thumbnail_file = '/thumbnails/image_thumb_normal_' . $upload->id . '.png';

            $content = Storage::disk('local')->get($thumbnail_file);
            Storage::put($thumbnail_file, $content, 'public');
            Storage::disk('local')->delete($thumbnail_file);

            $url = env('AWS_URL') . $thumbnail_file;
        
            $meta->thumbnail_normal = $url;
        }

        $upload->metadata = $meta;
        $upload->save();
    }

    public function extract(Upload $upload)
    {
        switch ($upload->file_type) {
            case 'audio':
                $this->extractAudio($upload);
                break;

            case 'video':
                $this->extractVideo($upload);
                break;
            
            case 'image':
                $this->extractImage($upload);
                break;

            default:                
                break;
        }
    }
}