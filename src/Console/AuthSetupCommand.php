<?php

namespace Origami\Auth\Console;

use Illuminate\Config\Repository;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Composer;
use Illuminate\Support\Facades\DB;
use Origami\Auth\Permission;
use Origami\Auth\Role;

class AuthSetupCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'auth:setup {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup permissions from the permissions.php config file';
    /**
     * @var
     */
    private $config;

    public function __construct(Repository $config)
    {
        parent::__construct();

        $this->config = $config;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        if ( ! $this->confirmToProceed() ) {
            return;
        }

        $this->cleanSlate();

        $this->createModelPermissions($this->config->get('permissions.models'));

        $this->createSimplePermissions($this->config->get('permissions.permissions'));

        $this->createRoles($this->config->get('permissions.roles'));

        $this->info('Saved permissions!');
    }

    protected function cleanSlate()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('user_role')->truncate();
        DB::table('roles')->truncate();
        DB::table('role_permission')->truncate();
        DB::table('permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    protected function createRoles(array $roles)
    {
        foreach ( $roles as $name => $attributes ) {

            $permissions = array_get($attributes, 'permissions');
            $attributes = array_except($attributes, 'permissions');

            if ( ! isset($attributes['name']) ) {
                $attributes['name'] = $name;
            }

            $role = Role::create($attributes);

            foreach ( $permissions as $name => $list ) {

                if ( ! is_array($list) ) {
                    $name = $list;
                }

                $model = ( class_exists($name) ? $name : null );

                if ( ! is_array($list) ) {
                    $list = is_null($model) ? [$name] : ['list','create','update','delete'];
                }

                foreach ( $list as $name ) {
                    $this->attachRolePermission($role, $name, $model);
                }

            }

            $this->info('Created role '.$role->name);
        }
    }

    protected function attachRolePermission(Role $role, $name, $model = null)
    {
        $permission = ! is_null($model) ? Permission::forModel($model, $name)->first() : Permission::name($name)->first();

        if ( ! $permission ) {
            return false;
        }

        return $role->grantPermission($permission);
    }

    protected function createModelPermissions(array $models)
    {
        foreach ( $models as $model => $permissions ) {

            if ( ! is_array($permissions) ) {
                $model = $permissions;
                $permissions = ['list', 'create', 'update', 'delete'];
            }

            foreach ( $permissions as $permission ) {
                Permission::make($permission, $model)->save();
            }

        }
    }

    protected function createSimplePermissions(array $permissions)
    {
        foreach ( $permissions as $name => $attributes ) {

            if ( is_int($name) ) {
                continue;
            }

            if ( ! is_array($attributes) ) {
                $attributes = [
                    'name' => $name,
                    'label' => $attributes
                ];
            }

            Permission::make($attributes)->save();

        }
    }
}
