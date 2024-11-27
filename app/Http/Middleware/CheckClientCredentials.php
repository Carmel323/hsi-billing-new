<?php 
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Passport\Http\Middleware\CheckClientCredentials as BaseMiddleware;

class CheckClientCredentials extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$scopes
     * @return mixed
     */
    public function handle($request, Closure $next, ...$scopes)
    {
        // Call the parent method if extending it or implement your logic here.
        try {
            $response = parent::handle($request, $next, ...$scopes);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Client authentication failed.',
                'message' => $e->getMessage()
            ], 401);
        }
    }
}