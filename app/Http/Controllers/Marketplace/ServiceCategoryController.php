<?php

namespace App\Http\Controllers\Marketplace;

use Illuminate\Http\Request;
use App\Models\ServiceCategory;
use App\Http\Controllers\Controller;

class ServiceCategoryController extends Controller
{
    protected $transformer = 'App\Transformers\Marketplace\ServiceCategoryTransformer';
    
    public function serviceCategories(Request $request)
    {
        $categories = ServiceCategory::get();

        return $this->response->collection($categories, $this->transformer);
    }
}