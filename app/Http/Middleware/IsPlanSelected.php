<?php

namespace App\Http\Middleware;

use App\Models\PaymentMethod;
use App\Models\SelectedPlan;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class IsPlanSelected
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Session::has('loginPartner')) {
            $selected_plan = SelectedPlan::where('zoho_cust_id', Session::get('loginId'))->first();
            $associated_payment = PaymentMethod::where('zoho_cust_id', Session::get('loginId'))->first();
            $isPlanSelected = $selected_plan &&  $associated_payment ? true : false;

            view()->share('isPlanSelected', $isPlanSelected);
            if ($request->is('/') && !$isPlanSelected) {
                return redirect('/select-plans');
            }
        }
        return $next($request);
    }
}
