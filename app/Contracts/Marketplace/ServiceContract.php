<?php

namespace App\Contracts\Marketplace;

interface ServiceContract
{
    /**
     * Creates the service
     * 
     * @param Array $data
     * 
     * @return \App\Models\Service
     */
    public function create($data);
}