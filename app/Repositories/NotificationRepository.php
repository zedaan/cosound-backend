<?php

namespace App\Repositories;

use App\Contracts\NotificationContract;
use App\Services\GetStreamService;

class NotificationRepository implements NotificationContract
{
    protected $getStreamService;

    public function __construct(GetStreamService $getStreamService)
    {
        $this->getStreamService = $getStreamService;
    }

    public function getNotifications($userId, $perPage = 20, $offset = 0, $options = [])
    {
        $notificationData = $this->getStreamService->getFeedActivityWithMeta($userId, 'notification', $perPage, $offset, $options);
        $notificationList = $notificationData['results'];

        $enrichedNotificationList = [];

        foreach ($notificationList as $notification) {
            $data = $notification;
            $data['activities'] = $this->getStreamService->enrich($notification['activities']);

            $enrichedNotificationList[] = $data;
        }

        $notificationData['results'] = $enrichedNotificationList;

        return $notificationData;
    }
}
