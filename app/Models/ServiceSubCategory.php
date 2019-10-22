<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceSubCategory extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'parent_id'];

    public $timestamps = false;

    public static function boot()
    {
        parent::boot();

        static::creating(function($subCategory) {
            $slug = str_slug($subCategory->name, '-');
            
            $count = static::whereRaw("slug RLIKE '^{$slug}(-[0-9]+)?$'")->count();

            $subCategory->slug = $count ? "{$slug}-{$count}" : $slug;
        });
    }
}
