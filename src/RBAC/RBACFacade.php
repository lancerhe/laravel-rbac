<?php
namespace LancerHe\RBAC;

use Illuminate\Support\Facades\Facade;


class RBACFacade extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'rbac';
    }
}