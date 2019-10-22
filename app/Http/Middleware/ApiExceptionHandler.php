<?php 

namespace App\Http\Middleware;

use Closure;

class ApiExceptionHandler 
{
	/**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

	public function handle($request, Closure $next)
    {
    	try {
    		return $next($request);
    	} catch(\HttpException $e) {
            return response()->json(['success' => false, 'error' => $e->getErrors(), 'File' => $e->getFile(), 'Line' => $e->getLine()], 403);
        } catch(\Watson\Validating\ValidationException $e) {
            return response()->json(['success' => false, 'error' => $e->getErrors(), 'File' => $e->getFile(), 'Line' => $e->getLine()], 403);
        } catch(\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage(), 'File' => $e->getFile(), 'Line' => $e->getLine()], 498);
        } catch(\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage(), 'File' => $e->getFile(), 'Line' => $e->getLine()], 498);
        } catch(\Exception $e) {
            if ($e->getCode())
                return response()->json(['success' => false, 'error' => $e->getMessage(), 'File' => $e->getFile(), 'Line' => $e->getLine()], 400);
            else
    		    return response()->json(['success' => false, 'error' => $e->getMessage(), 'File' => $e->getFile(), 'Line' => $e->getLine()], 400);
    	}
    }
}