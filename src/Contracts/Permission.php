<?php

namespace Origami\Auth\Contracts;

use Illuminate\Database\Eloquent\Model;

interface Permission
{

    /**
     * A permission can be applied to roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles();
    
}
