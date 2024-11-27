<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AuthCheck;
use App\Http\Middleware\AlreadyLoggedIn;
use App\Http\Middleware\Admin;
use App\Http\Middleware\AdminLogin;
use App\Http\Middleware\checkSubscripition;
use App\Http\Middleware\CheckSubscription;
use App\Http\Middleware\IsPlanSelected;
use App\Http\Middleware\Partner;
use App\Http\Middleware\PreventDuplicateSubscription;
use App\Http\Middleware\SuperAdmin;
use App\Models\SelectedPlan;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'isLoggedIn' => AuthCheck::class,
            'alreadyLoggedIn' => AlreadyLoggedIn::class,
            'isAdmin' => Admin::class,
            'isPartner' => Partner::class,
            'preventBack' => PreventDuplicateSubscription::class,
            'isSuperAdmin' => SuperAdmin::class,
            'isAdminLoggedIn' => AdminLogin::class,
            'checkSubscription' => CheckSubscription::class,
            'isPlanSelected' => IsPlanSelected::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            '/click-limit-notification',
            '/webhook/subscription',
            '/webhook/invoice',
            '/webhook/credit-note',
            '/webhook/payment-method',
            '/webhook/refund'

        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
