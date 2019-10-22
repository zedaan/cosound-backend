<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    protected $fillable = ['name', 'slug', 'description'];
    
    public $timestamps = false;

    public static function boot()
    {
        parent::boot();

        static::creating(function($category) {
            $slug = str_slug($category->name, '-');
            
            $count = static::whereRaw("slug RLIKE '^{$slug}(-[0-9]+)?$'")->count();

            $category->slug = $count ? "{$slug}-{$count}" : $slug;
        });
    }

    /**
     * Relations
     */

    public function subCategories()
    {
        return $this->hasMany(ServiceSubCategory::class, 'parent_id');
    }

}
