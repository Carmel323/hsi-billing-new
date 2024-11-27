<?php

namespace App\Http\Middleware;

use App\Models\Subscriptions;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Session::has('loginPartner')) {
            $subscription_live = Subscriptions::where('zoho_cust_id', Session::get('loginId'))->first();
            $hasLiveSubscription = $subscription_live ? true : false;
            view()->share('hasLiveSubscription', $hasLiveSubscription);
        }
        return $next($request);
    }
}
