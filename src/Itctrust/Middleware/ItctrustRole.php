<?php namespace Itctrust\Middleware;

/**
 * This file is part of Itctrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Itctrust
 */

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Config;

class ItctrustRole
{
    const DELIMITER = '|';

    protected $auth;

    /**
     * Creates a new instance of the middleware.
     *
     * @param Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Closure $next
     * @param  $roles
     * @return mixed
     */
    public function handle($request, Closure $next, $roles)
    {
        if (!is_array($roles)) {
            $roles = explode(self::DELIMITER, $roles);
        }
  
        if ($this->auth->guest() || !$request->user()->hasRole($roles)) {
            return call_user_func(
                Config::get('itctrust.middleware_handling', 'abort'),
                Config::get('itctrust.middleware_params', '403')
            );
        }

        return $next($request);
    }
}
