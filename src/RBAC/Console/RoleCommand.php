<?php namespace LancerHe\RBAC\Console;

use Illuminate\Console\Command;


class RoleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rbac:role
                            {roles?* : the name of new role, eg: admin \'common|common slug\'.}
                            {--create : create specify roles.} 
                            {--delete : delete specify roles.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'view, create or delete roles.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $roles = $this->parseRoles($this->argument('roles'));
        $delete = $this->option('delete');
        $create = $this->option('create');
        if (!$delete && !$create) {
            $this->showRoles();
        } else if ($create && $delete) {
            $this->error('Delete and create conflicts.');
        } else if ($create) {
            $this->createNewRoles($roles);
        } else if ($delete) {
            $this->deleteRoles($roles);
        }
    }

    /**
     * Create new role.
     *
     * @var $roles array  an array of role name.
     */
    public function createNewRoles($roles)
    {
        $class = config('rbac.role_model');
        if (class_exists($class))
        {
            foreach ($roles as $name => $slug)
            {
                if (!call_user_func_array([$class, 'getByName'], [$name]))
                {
                    $model = (new \ReflectionClass($class))->newInstance();
                    $model->name = $name;
                    $model->slug = $slug;
                    $model->save();
                    $this->info("{$name} role creates successfully.");
                } else {
                    $this->error("{$name} already exists.");
                }
            }

        } else {
            $this->error("{$class} does not exist.");
        }
    }

    /**
     * Parse roles
     *
     * @param $roles
     *
     * @return array
     */
    public function parseRoles($roles): array
    {
        $result = [];
        foreach ($roles as $role) {
            $role = explode('|', $role);
            $name = $role[0];
            $slug = $role[1] ?? $name;
            $result[$name] = $slug;
        }
        return $result;
    }

    /**
     * show role information.
     *
     */
    public function showRoles()
    {
        $headers = ['id', 'name', 'slug', 'created_at', 'updated_at'];
        $class = config('rbac.role_model');
        if (class_exists($class))
        {
            $result = call_user_func_array([$class, 'all'], []);
            $result = $result->toArray();
            $this->table($headers, $result);
        } else {
            $this->error("{$class} does not exist.");
        }
    }

    /**
     * Delete roles.
     *
     * @param $roles
     */
    public function deleteRoles($roles)
    {
        $class = config('rbac.role_model');
        if (class_exists($class))
        {
            foreach ($roles as $name => $slug)
            {
                $role = call_user_func_array([$class, 'getByName'], [$name]);
                if ($role)
                {
                    $role->delete();
                    $this->info("{$name} role has been deleted.");
                } else {
                    $this->error("{$name} doesn't exists.");
                }
            }
        } else {
            $this->error("{$class} does not exist.");
        }
    }
}