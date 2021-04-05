<?php
namespace LancerHe\RBAC\Middleware;

use Closure;
use Illuminate\Http\Request;


class Role
{
    const DELIMITER = '|';

    public function handle(Request $request, Closure $next, $roles='', $validateAll=false)
    {
        $roles = is_array($roles) ? $roles : explode(static::DELIMITER, $roles);

        $validateAll = is_bool($validateAll) ? $validateAll : filter_var($validateAll, FILTER_VALIDATE_BOOLEAN);
        if (!$request->user() or !$request->user()->hasRoles($roles, $validateAll)) {
            abort(403);
        }
        return $next($request);
    }
}