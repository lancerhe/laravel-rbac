<?php
namespace LancerHe\RBAC\Middleware;

use Closure;
use Illuminate\Http\Request;

class Ability
{
    const DELIMITER = '|';

    public function handle(Request $request, Closure $next, $roles, $permissions ='', $validateAll=false)
    {
        $roles = is_array($roles) ? $roles : explode(static::DELIMITER, $roles);
        $permissions = is_array($permissions) ? $permissions : explode(static::DELIMITER, $permissions);
        $validateAll = is_bool($validateAll) ? $validateAll : filter_var($validateAll, FILTER_VALIDATE_BOOLEAN);
        if (!$request->user() or ! $request->user()->ability($roles, $permissions, ['validate_all' => $validateAll])) {
            abort(403);
        }
        return $next($request);
    }
}