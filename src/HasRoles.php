<?php

namespace Origami\Auth;

trait HasRoles
{

    /**
     * A user may have multiple roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    /**
     * Assign the given role to the user.
     *
     * @param  string $role
     * @return mixed
     */
    public function assignRole($role)
    {
        return $this->roles()->save(
            Role::whereName($role)->firstOrFail()
        );
    }

    /**
     * Determine if the user has the given role.
     *
     * @param  mixed $role
     * @return boolean
     */
    public function hasRole($role)
    {
        if ( is_string($role) ) {
            return $this->roles->contains('name', $role);
        }

        return ! $role->intersect($this->roles)->isEmpty();
    }

    /**
     * Determine if the user may perform the given permission.
     *
     * @param  Permission $permission
     * @return boolean
     */
    public function hasPermission($permission, $model = null)
    {
        return app(Drawbridge::class)->check($this, $permission, $model);
        //return $this->hasRole($permission->roles);
    }

}
