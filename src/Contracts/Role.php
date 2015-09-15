<?php

namespace Origami\Auth\Contracts;

use Origami\Auth\Permission;
use Illuminate\Database\Eloquent\Model;

interface Role
{

    /**
     * A role may be given various permissions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions();

    /**
     * Grant the given permission to a role.
     *
     * @param  Permission $permission
     * @return mixed
     */
    public function grantPermission(Permission $permission);

}
