<?php

namespace Origami\Auth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Drawbridge
{
    /**
     * Holds the cache of user's permissions.
     *
     * @var array
     */
    protected $cache = [];

    /**
     * Determine if the given user has the given permission.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $user
     * @param  string  $permission
     * @param  array|null  $additional
     * @return bool
     */
    public function check(Model $user, $permission, $additional = null)
    {
        $permissions = $this->grabUserPermissions($user);

        foreach ( $this->compileRequestedPermission($permission, $additional) as $permission ) {
            if ( $permissions->contains($permission) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Compile a list of permissions that match the provided parameters.
     *
     * @param  string  $permission
     * @param  array|null  $additional
     * @return array
     */
    protected function compileRequestedPermission($permission, $additional)
    {
        if ( is_null($additional) ) {
            return [$permission];
        }

        return $this->compileModelPermissions($permission, $additional);
    }

    /**
     * Compile a list of permissions that match the given model.
     *
     * @param  string  $permission
     * @param  array|null  $additional
     * @return array
     */
    protected function compileModelPermissions($permission, $additional)
    {
        $model = reset($additional);
        $model = $model instanceof Model ? $model : new $model;

        $permission = [
            'name'        => $permission,
            'entity_id'   => null,
            'entity_type' => $model->getMorphClass(),
        ];

        // If the provided model does not exist, we will only look for permissions
        // where the "entity_id" is null. If the model does exist, we'll also
        // look for the permissions whose "entity_id" matches the model key.
        if ( ! $model->exists ) {
            return [$permission];
        }

        return [
            $permission,
            array_merge($permission, ['entity_id' => $model->getKey()])
        ];
    }

    /**
     * Get the given user's permissions.
     *
     * @param  \Illuminate\Database\Eloquent\Model $user
     * @param bool $cached
     * @return \Illuminate\Support\Collection
     */
    public function grabUserPermissions(Model $user, $cached = true)
    {
        $id = $user->getKey();

        if ( ! isset($this->cache[$id]) || ! $cached ) {
            $this->cache[$id] = $this->loadUserPermissions($user);
        }

        return $this->cache[$id];
    }

    /**
     * Clear the permissions cache.
     *
     * @return $this
     */
    public function clear()
    {
        $this->cache = [];

        return $this;
    }

    /**
     * Clear the permissions cache for the given user.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $user
     * @return $this
     */
    public function clearForUser(Model $user)
    {
        unset($this->cache[$user->getKey()]);

        return $this;
    }

    /**
     * Get a fresh list of the given user's permissions.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $user
     * @return \Illuminate\Support\Collection
     */
    protected function loadUserPermissions(Model $user)
    {
        $query = $this->buildPermissionRolesQuery($user);

        $permissions = $query->getQuery()->select('name', 'entity_id', 'entity_type')->get();

        return Collection::make($permissions)->map(function ($permission) {
            $simple = is_null($permission->entity_id) && is_null($permission->entity_type);

            return $simple ? $permission->name : (array) $permission;
        });
    }

    /**
     * Constrain a roles query by the given user.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function buildPermissionRolesQuery(Model $user)
    {
        $roles = $user->roles;

        return Permission::whereHas('roles', function ($query) use ($roles) {
            return $query->whereIn('roles.id', $roles->lists('id')->all());
        });
    }

}
