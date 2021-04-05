<?php
namespace LancerHe\RBAC\Console;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Console\RouteListCommand;

class PermissionGenerateCommand extends RouteListCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'rbac:permission-generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'laravel-rbac permission quickly operation';

    /**
     * An array of all the registered routes.
     *
     * @var \Illuminate\Routing\RouteCollection
     */
    protected $routes;

    /**
     * The route service.
     *
     * @var Router
     */
    protected $router;

    /**
     *
     * permission class name
     *
     * @var $permission
     */

    protected $permission;


    /**
     * PermissionCommand constructor.
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        parent::__construct($router);
        $this->permission = config('rbac.permission_model');
    }

    public function handle()
    {
        $systemPermissions = $this->getSystemPermissions();
        $this->info("\nAll permissions has existed as follows:\n");
        $this->table(['id', 'name', 'slug', 'http_method', 'http_path', 'created_at', 'updated_at'],
            $systemPermissions);

        $systemPermissionNames = collect($systemPermissions)->pluck('name')->all();

        $permissions = collect($this->getPermissions())->filter(function ($item, $index) use ($systemPermissionNames){
            return !in_array($item['name'], $systemPermissionNames);
        })->all();
        $this->info("\nNew add permissions as follows:\n");
        $this->table(['name', 'slug', 'http_method', 'http_path'], $permissions);
        $this->createNewPermissions($permissions);
    }

    /**
     * Get all system permissions by routes.
     *
     * @return array
     */
    public function getPermissions()
    {
        $permissions = [];
        foreach ($this->getRoutes() as $route)
        {
            $permissions[] = [
                'name' => "[${route['method']}]${route['uri']}",
                'slug' => $route['name'] ?? '',
                'http_method' => $route['method'],
                'http_path' => $route['uri']
            ];
        }

        return $permissions;
    }

    /**
     * Create new permissions.
     *
     * @param array $permissions
     */
    public function createNewPermissions(array $permissions)
    {
        if ($this->confirm('Confirm to generate permissions?')) {
            foreach ($permissions as $permission) {
                if (!($this->permission)::getByName($permission['name'])) {
                    $model = new $this->permission;
                    $model->name = $permission['name'];
                    $model->slug = $permission['slug'];
                    $model->http_path = $permission['http_path'];
                    $model->http_method = $permission['http_method'];
                    $model->save();
                }
            }
        }
    }

    /**
     * Get System Permissions.
     *
     * @return array
     */
    public function getSystemPermissions(): array
    {
        return (new $this->permission)->all()->toArray();
    }
}