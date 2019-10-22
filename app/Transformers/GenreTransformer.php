<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

use App\Models\Genre;

class GenreTransformer extends TransformerAbstract
{
    /**
     * Turn this item object into a generic array
     *
     * @return array
     */
    public function transform(Genre $genre)
    {
        return [
            'value'    => $genre->id,
            'label'    => $genre->name
        ];
    }
}