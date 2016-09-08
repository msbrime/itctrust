<?php namespace Itctrust\Traits;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;

trait ItctrustMandateTrait
{
    /**
     * Big block of caching functionality
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function cachedPermissionSets()
    {
        $cacheKey = 'itctrust_permission_sets_for_mandate_' . $this->getKey();

        return Cache::remember($cacheKey, Config::get('cache.ttl', 60), function () {
            return $this->permissionSets()->get();
        });
    }

    /**
     * Many-to-Many relations with the user model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(
            Config::get('auth.providers.users.model'),
            Config::get('itctrust.mandate_user_table'),
            Config::get('itctrust.mandate_foreign_key'),
            Config::get('itctrust.user_foreign_key')
        );
    }

    /**
     * Many-to-Many relations with the permission model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissionSets()
    {
        return $this->belongsToMany(
            Config::get('itctrust.permission_set'),
            Config::get('itctrust.mandate_permission_set_table'),
            Config::get('itctrust.mandate_foreign_key'),
            Config::get('itctrust.permission_set_foreign_key')
        );
    }

    /**
     * Boot the mandate model
     * Attach event listener to remove the many-to-many records when trying to delete
     * Will NOT delete any records if the mandate model uses soft deletes.
     *
     * @return void|bool
     */
    public static function bootItctrustMandateTrait()
    {
        $flushCache = function ($mandate) {
            $mandate->flushCache();
            return true;
        };
        
        // If the mandate doesn't use SoftDeletes
        if (method_exists(Config::get('itctrust.mandate'), 'restored')) {
            static::restored($flushCache);
        }

        static::deleted($flushCache);
        static::saved($flushCache);

        static::deleting(function ($mandate) {
            if (!method_exists(Config::get('itctrust.mandate'), 'bootSoftDeletes')) {
                $mandate->users()->sync([]);
                $mandate->permissionSets()->sync([]);
            }

            return true;
        });
    }
    
    /**
     * Checks if the mandate has a permission by its name.
     *
     * @param string|array $name       Permission name or array of permission names.
     * @param bool         $requireAll All permissions in the array are required.
     *
     * @return bool
     */
    public function hasPermission($name, $requireAll = false, $returnAsArray = false)
    {

        if(!is_array($name)){
            $name = [$name];
        }

        $availablePermissions = [];

        foreach($this->cachedPermissionSets() as $permissionSet){

            $isPermitted = $permissionSet->hasPermission($name,false,true);

            if($isPermitted && !$requireAll && !$returnAsArray){
                return true;
            }

            $availablePermissions = array_merge($availablePermissions,$isPermitted);

            if(!array_diff($name,$availablePermissions)){
                return ($returnAsArray) ? $availablePermissions : true;
            }

        }
        
        return ($returnAsArray) ? $availablePermissions : (Boolean) !array_diff($name,$availablePermissions);
    
    }

    /**
     * Checks if the mandate has a permission by its name.
     *
     * @param string|array $name       Permission name or array of permission names.
     * @param bool         $requireAll All permissions in the array are required.
     *
     * @return bool
     */
    public function hasPermissionSet($set, $requireAll = false)
    {
        if (is_array($set)) {
            foreach ($set as $permissionSet) {
                $hasPermissionSet = $this->hasPermissionSet($permissionSet);

                if ($hasPermissionSet && !$requireAll) {
                    return true;
                } elseif (!$hasPermissionSet && $requireAll) {
                    return false;
                }
            }

            // If we've made it this far and $requireAll is FALSE, then NONE of the permissions were found
            // If we've made it this far and $requireAll is TRUE, then ALL of the permissions were found.
            // Return the value of $requireAll;
            return $requireAll;
        }

        foreach ($this->cachedPermissionSets() as $permissionSet) {
            if ($permissionSet->name == $set) {
                return true;
            }
        }

        return false;
    }

   /**
     * Save the inputted permissions.
     *
     * @param mixed $inputPermissions
     *
     * @return array
     */
    public function savePermissionSets($inputPermissionSets)
    {
        // If the inputPermissions ist empty it will delete all associations
        $changes = $this->permissionSets()->sync($inputPermissionSets);
        $this->flushCache();

        return $changes;
    }

    /**
     * Attach permission to current mandate.
     *
     * @param object|array $permission
     *
     * @return void
     */
    public function attachPermissionSet($permissionSet)
    {
        if (is_object($permissionSet)) {
            $permissionSet = $permissionSet->getKey();
        }

        if (is_array($permissionSet)) {
            $permissionSet = $permissionSet['id'];
        }

        $this->permissionSets()->attach($permissionSet);
        $this->flushCache();

        return $this;
    }

    /**
     * Detach permission from current mandate.
     *
     * @param object|array $permission
     *
     * @return void
     */
    public function detachPermissionSet($permissionSet)
    {
        if (is_object($permissionSet)) {
            $permissionSet = $permissionSet->getKey();
        }

        if (is_array($permissionSet)) {
            $permissionSet = $permissionSet['id'];
        }

        $this->permissionSets()->detach($permissionSet);
        $this->flushCache();

        return $this;
    }

    /**
     * Attach multiple permissions to current mandate.
     *
     * @param mixed $permissions
     *
     * @return void
     */
    public function attachPermissionSets($permissionSets)
    {
        foreach ($permissionSets as $permissionSet) {
            $this->attachPermissionSet($permissionSet);
        }

        return $this;
    }

    /**
     * Detach multiple permissions from current mandate
     *
     * @param mixed $permissions
     *
     * @return void
     */
    public function detachPermissionSets($permissionSets)
    {
        foreach ($permissionSets as $permissionSet) {
            $this->detachPermissionSet($permissionSet);
        }

        return $this;
    }

    /**
     * Flush the mandate's cache
     * @return void
     */
    public function flushCache()
    {
        Cache::forget('itctrust_permission_sets_for_mandate_' . $this->getKey());
    }
}
