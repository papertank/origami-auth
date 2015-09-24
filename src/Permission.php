<?php

namespace Origami\Auth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Permission extends Model
{

    protected $table = 'permissions';
    protected $fillable = ['name','label','entity_type','entity_id'];

    public static function make($name, $model = null)
    {
        $attributes = is_array($name) ? $name : ['name' => $name];

        if ( ! is_null($model) ) {
            $model = $model instanceof Model ? $model : new $model;
            $attributes['entity_type'] = $model->getMorphClass();
            $attributes['entity_id'] = $model->exists ? $model->getKey() : null;
        }

        if ( ! isset($attributes['label']) ) {
            $attributes['label'] = Str::title(str_replace(['-','_'], ' ',$name) . ( $model ? ' '.$model->getTable() : '' ));
        }

        return new static($attributes);
    }

    /* Query Helpers */

    public function scopeName($query, $name)
    {
        return $query->where('name', '=', $name);
    }

    public function scopeForModel($query, $model, $name)
    {
        $entity = $model instanceof Model ? get_class($model) : $model;

        return $query->where(function($q) use($model, $entity, $name)
        {
            if ( $model instanceof Model && $model->exists ) {
                $q->where('entity_id', '=', $model->getKey());
            } else {
                $q->whereNull('entity_id');
            }

            $q->where('entity_type', '=', $entity)
                ->where('name', '=', $name);
        });
    }

    /**
     * A permission can be applied to roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }
    
}
