<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

use App\Models\Upload;

class UploadTransformer extends TransformerAbstract
{
    /**
     * Turn this item object into a generic array
     *
     * @return array
     */
    public function transform(Upload $upload)
    {
        $body = $upload->uploadable->body ?? null;

        return [
            'id' => $upload->id,
            'body' => $body,
            'path' => $upload->path,
            'file_type' => $upload->file_type,
            'metadata' => $upload->metadata,
            'created_at' => $upload->created_at,
        ];
    }
}