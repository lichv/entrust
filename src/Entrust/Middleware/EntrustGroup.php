<?php
namespace Lichv\Entrust\Middleware;

/**
 * This file is part of Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Lichv\Entrust
 */

use Closure;
use Illuminate\Contracts\Auth\Guard;

class EntrustGroup
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
	public function handle($request, Closure $next, $groups)
	{
		if (!is_array($groups)) {
			$groups = explode(self::DELIMITER, $groups);
		}

		if ($this->auth->guest() || !$request->user()->hasGroup($groups)) {
			abort(403);
		}

		return $next($request);
	}
}
