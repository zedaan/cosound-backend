<?php

namespace App\Contracts;

interface NotificationContract
{
    /**
     * Read notification
     * 
     * @param UUID $userId
     * @param integer $perPage
     * @param integer $offset
     * 
     * @return Array Response
     */
    public function getNotifications($userId, $perPage, $offset);
}