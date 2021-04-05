<?php
namespace LancerHe\RBAC;

use Illuminate\Foundation\Application;

class RBAC
{
    /**
     * Laravel application
     *
     * @var \Illuminate\Foundation\Application
     */

    private $app;

    /**
     * RBAC constructor.
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @return mixed
     */
    public function user()
    {
        return $this->app->auth->user();
    }

    public function hasRoles($roles, $requireAll=false)
    {
        return $this->user() and $this->user()->hasRoles($roles, $requireAll);
    }

    public function hasPermissions($permissions, $requireAll=false)
    {
        return $this->user() and $this->user()->hasPermissions($permissions, $requireAll);
    }

    public function ability($roles = [], $permissions = [], array $options = [])
    {
        return $this->user() and $this->user()->ability($roles, $permissions, $options);
    }
}