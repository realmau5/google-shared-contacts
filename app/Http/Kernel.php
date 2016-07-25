<?php

namespace GContacts\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware
        = [
            \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
            \GContacts\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \GContacts\Http\Middleware\VerifyCsrfToken::class,
        ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware
        = [
            'auth'                 => \GContacts\Http\Middleware\Authenticate::class,
            'auth.basic'           => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'auth.google'          => \GContacts\Http\Middleware\AuthGoogle::class,
            'auth.google.reversed' => \GContacts\Http\Middleware\AuthGoogleReversed::class,
            'guest'                => \GContacts\Http\Middleware\RedirectIfAuthenticated::class,
            'secure'               => \GContacts\Http\Middleware\Secure::class,
        ];
}
