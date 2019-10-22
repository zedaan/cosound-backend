<?php

namespace App\Repositories;

use App\Services\GetStreamService;

use App\Contracts\FeedContract;

class FeedRepository implements FeedContract
{
    protected $getStreamService;

    public function __construct(GetStreamService $service)
    {
        $this->getStreamService = $service;
    }

    /**
	 * {@inheritdoc}
	 */
    public function getFeed($userId, $perPage = 20, $offset = 0)
    {
        $activities = $this->getStreamService->getFeedActivity($userId, 'timeline', $perPage, $offset);
        return $this->getStreamService->enrich($activities);
    }
}
