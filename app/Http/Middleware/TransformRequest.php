<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TransformsRequest as Middleware;

class TransformRequest extends Middleware
{
	protected function transform($key, $value)
	{
		if ($value === 'true' || $value === 'TRUE') return true;
		if ($value === 'false' || $value === 'FALSE') return false;
		
		return $value;
	}
}