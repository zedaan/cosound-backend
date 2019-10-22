<?php

use Carbon\Carbon;
use Illuminate\Support\Debug\Dumper;

if (! function_exists('dateFormatter')) {

    /**
     * Date Formatter according to app.timezone
     * 
     * @param  String $dateString 
     * @return Date             
     */
    
    function dateFormatter($dateString) {

        if ( is_null($dateString)) return NULL;
        
        $timezone = config('app.timezone');
        $dateFormat = new Carbon($dateString, $timezone);
        return $dateFormat->format('Y-m-d');
    }
}

if (! function_exists('sortResults')) {
    function sortResults($query, $input, $allowedFields = [])
    {
        $order = substr($input,0,1);
        $field = ltrim($input,'-');

        if (in_array($field, $allowedFields) )
            $query = $order == '-' ? $query->orderBy($field,'desc') : $query->orderBy($field);

        return $query;
    }
}

if (! function_exists('filterResults')) {
    function filterResults($query, $searchString, $searchFields = [])
    {
        $query = $query->where(function ($subQuery) use ($searchString, $searchFields) {
            foreach ($searchFields as $field) {
                $subQuery = $subQuery->orWhereRaw($field . ' like ?', [$searchString]);
                // $subQuery = $subQuery->orWhere($field, 'like', $searchString);
            }
        });

        return $query;
    }
}

if (! function_exists('filterNullKeys')) {
    function filterNullKeys($array)
    {
        $filteredArray = array_filter($array, function($element){
            return !is_null($element);
        });
        
        return $filteredArray;
    }
}

if (! function_exists('getTokenExpiryTime')) {
    function getTokenExpiryTime($ttl) {
        return Carbon::now()->addSeconds($ttl)->format('Y-m-d\TH:i:s\Z');
    }
}