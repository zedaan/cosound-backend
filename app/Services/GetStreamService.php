<?php

namespace App\Services;

use GetStream;
use GetStream\StreamLaravel\Enrich;

class GetStreamService
{
    protected $client, $enricher;
    protected $allowedGroups = ['timeline', 'notification', 'message'];

    public function __construct()
    {
        $this->client = new GetStream\Stream\Client(env('STREAM_API_KEY'), env('STREAM_SECRET'), 'v1.0', '', env('STREAM_TIMEOUT'));
        $this->enricher = new Enrich();
    }

    public function enrich($activities)
    {
        return $this->enricher->enrichActivities($activities);
    }

    public function getToken($userId, $group)
    {
        if (! in_array($group, $this->allowedGroups))
            throw new \Exception("Invalid feed group '$group'");

        return $this->client->feed($group, $userId)->getReadonlyToken();
    }

    function addFeedActivity($userId, $group, $data)
	{
        $groupFeed = $this->client->feed($group, $userId);
        return $groupFeed->addActivity($data);
    }

    function removeFeedActivity($userId, $group, $foreignId)
    {
        $groupFeed = $this->client->feed($group, $userId);
        return $groupFeed->removeActivity($foreignId, true);
    }

    function getFeedActivity($userId, $group, $perPage, $offset, $options = [])
	{
        return $this->getFeedActivityWithMeta($userId, $group, $perPage, $offset, $options)['results'];
    }

    function getFeedActivityWithMeta($userId, $group, $perPage, $offset, $options = [])
	{
        $groupFeed = $this->client->feed($group, $userId);
        return $groupFeed->getActivities($offset, $perPage, $options);
    }

    public function addUserFeed($userId, $feed)
	{
        return $this->addFeedActivity($userId, 'user', $feed);
    }

    public function removeUserFeed($userId, $foreignId)
    {
        return $this->removeFeedActivity($userId, 'user', $foreignId);
    }

    public function sendNotification($userId, $notification)
    {
        return $this->addFeedActivity($userId, 'notification', $notification);
    }

    public function removeNotification($userId, $foreignId)
    {
        return $this->removeFeedActivity($userId, 'notification', $foreignId);
    }

    public function followUserFeed($followedUserId, $followerUserId)
    {
        $timeline = $this->client->feed('timeline', $followerUserId);
        return $timeline->follow('user', $followedUserId);
    }

    public function unfollowUserFeed($followedUserId, $followerUserId)
    {
        $timeline = $this->client->feed('timeline', $followerUserId);
        return $timeline->unfollow('user', $followedUserId);
    }
}