<?php

namespace GSharedContacts\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Session;

/**
 * Class AuthGoogleReversed
 *
 * @package GSharedContacts\Http\Middleware
 */
class AuthGoogleReversed
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard $auth
     *
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Session::has('access_token')) {
            $token        = Session::get('access_token');
            $expireMoment = $token['created'] + $token['expires_in'];
            $time         = time();
            if ($time <= $expireMoment) {
                return redirect(route('home'));
            }
        }

        return $next($request);
    }
}
