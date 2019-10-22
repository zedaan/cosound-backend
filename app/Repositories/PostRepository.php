<?php

namespace App\Repositories;

use App\Contracts\PostContract;

use App\Models\Post;

class PostRepository implements PostContract
{
	/**
	 * {@inheritdoc}
	 */
	public function createPost($data)
	{
        $postData = [];
		$postData = [
			'body' => array_get($data, 'body', NULL),
			'user_id' => array_get($data, 'user_id'),
			'parent_id' => array_get($data, 'parent_id', NULL),
		];

		$post = new Post;

        $post->fill($postData)->save();
        
        return $post;
    }
}
