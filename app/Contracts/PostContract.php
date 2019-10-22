<?php

namespace App\Contracts;

interface PostContract
{
    /**
     * Save Post
     * 
     * @param Array $data
     * 
     * @return Post Response
     */
    public function createPost($data);
}