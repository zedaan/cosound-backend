<?php

namespace App\Contracts;

interface FeedContract
{
    /**
     * Read feeds from timeline
     * 
     * @param UUID $userId
     * @param integer $perPage
     * @param integer $offset
     * 
     * @return Array Response
     */
    public function getFeed($userId, $perPage, $offset);
}