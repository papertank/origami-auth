<?php

namespace Origami\Auth\Contracts;

interface HasRoles {

    /**
     * A user may have multiple roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles();

    /**
     * Assign the given role to the user.
     *
     * @param  string $role
     * @return mixed
     */
    public function assignRole($role);

    /**
     * Determine if the user has the given role.
     *
     * @param  mixed $role
     * @return boolean
     */
    public function hasRole($role);

    /**
     * Determine if the user may perform the given permission.
     *
     * @param  mixed $permission
     * @return boolean
     */
    public function hasPermission($permission);

}