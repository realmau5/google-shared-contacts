<?php

namespace GContacts\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Redirect;
use Session;

/**
 * Class AuthGoogle
 *
 * @package GContacts\Http\Middleware
 */
class AuthGoogle
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
            $expireMoment = $token->created + $token->expires_in;
            $time         = time();
            if ($time > $expireMoment) {
                Session::forget('access_token');
                return Redirect::route('index');
            }
        } else {
            return Redirect::route('index');
        }
        return $next($request);
    }
}
