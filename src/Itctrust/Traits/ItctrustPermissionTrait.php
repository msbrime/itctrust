<?php namespace Itctrust\Traits;

use Illuminate\Support\Facades\Config;

trait ItctrustPermissionTrait
{

    /**
     * Many-to-Many relations with permission sets
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissionSets()
    {
        return $this->belongsToMany(
            Config::get('itctrust.permission_set'),
            Config::get('itctrust.permission_permission_set_table')
        );
    }

    /**
     * Boot the permission model
     * Attach event listener to remove the many-to-many records when trying to delete
     * Will NOT delete any records if the permission model uses soft deletes.
     *
     * @return void|bool
     */
    public static function bootItctrustPermissionTrait()
    {
        static::deleting(function ($permission) {
            if (!method_exists(Config::get('itctrust.permission'), 'bootSoftDeletes')) {
                $permission->permissionSets()->sync([]);
            }

            return true;
        });
    }

}