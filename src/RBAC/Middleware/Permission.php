<?php
namespace LancerHe\RBAC\Middleware;


use Closure;
use Illuminate\Http\Request;

class Permission
{
    const DELIMITER = '|';

    public function handle(Request $request, Closure $next, $permissions ='', $validateAll=false)
    {
        $permissions = is_array($permissions) ? $permissions : explode(static::DELIMITER, $permissions);
        $validateAll = is_bool($validateAll) ? $validateAll : filter_var($validateAll, FILTER_VALIDATE_BOOLEAN);
        if (!$request->user() or !$request->user()->hasPermissions($permissions, $validateAll)) {
            abort(403);
        }
        return $next($request);
    }

}