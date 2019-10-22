<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseApiController;

use Illuminate\Http\Request;

class GenreController extends BaseApiController
{
    protected $model = 'App\Models\Genre';
    protected $transformer = 'App\Transformers\GenreTransformer';
    protected $per_page = 100;
}
