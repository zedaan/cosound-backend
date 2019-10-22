<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Alsofronie\Uuid\UuidModelTrait;

class Post extends Model
{
    use UuidModelTrait;
    
    public $incrementing = false;
    
    protected $fillable = [
        'body', 'user_id', 'parent_id'
    ];

    protected static function boot() {
        parent::boot();
    
        static::deleting(function($post) {
            $post->comments()->delete();
        });
    }

    /**
     * Relations
     */

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function getCommentCountAttribute()
    {
        return $this->comments()->count();
    }

    public function media()
    {
        return $this->morphMany(Upload::class, 'uploadable');
    }

    public function postedBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent()
    {
        return $this->belongsTo(Post::class, 'parent_id');
    }

    public function childs()
    {
        return $this->hasMany(Post::class, 'parent_id');
    }
}
