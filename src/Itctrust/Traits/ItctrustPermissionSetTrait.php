<?php namespace Itctrust\Traits;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;

trait ItctrustPermissionSetTrait
{

        /**
     * Big block of caching functionality
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function cachedPermissions()
    {
        $cacheKey = 'itctrust_permissions_for_permission_set_' . $this->getKey();

        return Cache::remember($cacheKey, Config::get('cache.ttl', 60), function () {
            return $this->permissions()->get();
        });
    }


	public function permissions(){

		return $this->belongsToMany(
				Config::get('itctrust.permission'),
				Config::get('itctrust.permission_permission_set_table'),
				Config::get('itctrust.permission_set_foreign_key')
			);

	}

	public function roles(){

		return $this->belongsToMany(
				Config::get('itctrust.role'),
				Config::get('itctrust.permission_set_role_table'),
				Config::get('itctrust.permission_set_foreign_key'),
				Config::get('itctrust.role_foreign_key')
			);

	}

	public static function bootItctrustPermissionSetTrait(){

		static::deleting(function($permissionSet){

            if (!method_exists(Config::get('itctrust.permission_set'), 'bootSoftDeletes')) {
                $permissionSet->roles()->sync([]);
                $permissionSet->permissions()->sync([]);
            }

            return true;

		});

	}
    
     /**
     * Checks if the permission_set has a permission by its name.
     *
     * @param string|array $name       Permission name or array of permission names.
     * @param bool         $requireAll All permissions in the array are required.
     * @param bool         $returnAsArray returns evaluation as an array or a boolean
     *
     * @return bool | array
     */
    public function hasPermission($name, $requireAll = false, $returnAsArray = false)
    {

        /**
         * check if a single or array of permissions were passed
         */
        if(is_array($name)){

            /**
             * variable to store the available permissions
             * while they are evaluated
             * @var array
             */
            $availablePermissions = [];

            foreach($name as $permissionName){

                /**
                 * evaluate each permission individual through a recursive call
                 * to this function
                 * @var array
                 */
                $isPermitted = $this->hasPermission($permissionName,$requireAll,true);

                /**
                 * if the permission is found
                 */
                if($isPermitted){

                    if($requireAll || $returnAsArray){

                        /**
                         * update the available permissions with the current permission
                         * @var array
                         */
                        $availablePermissions = array_merge($availablePermissions,$isPermitted);

                    }else{

                        /**
                         * return the boolean value if the not all the permissions are 
                         * required
                         */
                        return (Boolean) $isPermitted;
                    }

                }else{  

                    /**
                     * if all the permissions are required then 
                     * return 
                     */
                    if($requireAll && !$returnAsArray){

                        return ($returnAsArray) ? [] : false;

                    }

                }

            }

            /**
             * if we've gotten this far then it means that not all the
             * permissions are required, but should be returned as an array
             */
            return (($returnAsArray) ? $availablePermissions : (Boolean) !array_diff($name,$availablePermissions));

        }
        else{

            /**
             * get the permissions associated to the
             * permission set
             * @var [type]
             */
            $permissions = $this->cachedPermissions();

            foreach($permissions as $permission){

                if($permission->name === $name){

                    return ($returnAsArray) ? [$name] : true;

                }

            }

            return [];

        }

    }    
   
    /**
     * Save the inputted permissions.
     *
     * @param mixed $inputPermissions
     *
     * @return array
     */
    public function savePermissions($inputPermissions)
    {
        // If the inputPermissions ist empty it will delete all associations
        $changes = $this->permissions()->sync($inputPermissions);
        $this->flushCache();

        return $changes;
    }

    /**
     * Attach permission to current role.
     *
     * @param object|array $permission
     *
     * @return void
     */
    public function attachPermission($permission)
    {
        if (is_object($permission)) {
            $permission = $permission->getKey();
        }

        if (is_array($permission)) {
            $permission = $permission['id'];
        }

        $this->permissions()->attach($permission);
        $this->flushCache();

        return $this;
    }

    /**
     * Detach permission from current role.
     *
     * @param object|array $permission
     *
     * @return void
     */
    public function detachPermission($permission)
    {
        if (is_object($permission)) {
            $permission = $permission->getKey();
        }

        if (is_array($permission)) {
            $permission = $permission['id'];
        }

        $this->permissions()->detach($permission);
        $this->flushCache();

        return $this;
    }

    /**
     * Attach multiple permissions to current role.
     *
     * @param mixed $permissions
     *
     * @return void
     */
    public function attachPermissions($permissions)
    {
        foreach ($permissions as $permission) {
            $this->attachPermission($permission);
        }

        return $this;
    }

    /**
     * Detach multiple permissions from current role
     *
     * @param mixed $permissions
     *
     * @return void
     */
    public function detachPermissions($permissions)
    {
        foreach ($permissions as $permission) {
            $this->detachPermission($permission);
        }

        return $this;
    }

    /**
     * Flush the role's cache
     * @return void
     */
    public function flushCache()
    {
        Cache::forget('itctrust_permissions_for_permission_set_' . $this->getKey());
    }
}
