<?php namespace Itctrust\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

trait ItctrustUserTrait
{
    /**
     * Tries to return all the cached roles of the user
     * and if it can't bring the roles from the cache,
     * it would bring them back from the DB
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function cachedRoles()
    {
        $cacheKey = 'itctrust_roles_for_user_' . $this->getKey();

        return Cache::remember($cacheKey, Config::get('cache.ttl', 60), function () {
            return $this->roles()->get();
        });
    }


    /**
     * Tries to return all the cached roles of the user
     * and if it can't bring the roles from the cache,
     * it would bring them back from the DB
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function cachedMandates()
    {
         return $this->mandates()->get();
        $cacheKey = 'itctrust_mandates_for_user_' . $this->getKey();

        return Cache::remember($cacheKey, Config::get('cache.ttl', 60), function () {
           
        });
    }

    /**
     * Many-to-Many relations with Role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(
            Config::get('itctrust.role'),
            Config::get('itctrust.role_user_table'),
            Config::get('itctrust.user_foreign_key'),
            Config::get('itctrust.role_foreign_key')
        );
    }

    /**
     * Many-to-Many relations with Role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function mandates()
    {
        return $this->belongsToMany(
            Config::get('itctrust.mandate'),
            Config::get('itctrust.mandate_user_table'),
            Config::get('itctrust.user_foreign_key'),
            Config::get('itctrust.mandate_foreign_key')
        );
    }

    /**
     * Boot the user model
     * Attach event listener to remove the many-to-many records when trying to delete
     * Will NOT delete any records if the user model uses soft deletes.
     *
     * @return void|bool
     */
    public static function bootItctrustUserTrait()
    {
        $flushCache = function ($user) {
            $user->flushCache();
            return true;
        };

        // If the user doesn't use SoftDeletes
        if (method_exists(Config::get('auth.providers.users.model'), 'restored')) {
            static::restored($flushCache);
        }

        static::deleted($flushCache);
        static::saved($flushCache);

        static::deleting(function ($user) {
            if (!method_exists(Config::get('auth.providers.users.model'), 'bootSoftDeletes')) {
                $user->roles()->sync([]);
            }

            return true;
        });
    }

    /**
     * Checks if the user has a role by its name.
     *
     * @param string|array $name       Role name or array of role names.
     * @param bool         $requireAll All roles in the array are required.
     *
     * @return bool
     */
    public function hasRole($name, $requireAll = false)
    {
        if (is_array($name)) {
            if (empty($name)) {
                return true;
            }

            foreach ($name as $roleName) {
                $hasRole = $this->hasRole($roleName);

                if ($hasRole && !$requireAll) {
                    return true;
                } elseif (!$hasRole && $requireAll) {
                    return false;
                }
            }

            // If we've made it this far and $requireAll is FALSE, then NONE of the roles were found
            // If we've made it this far and $requireAll is TRUE, then ALL of the roles were found.
            // Return the value of $requireAll;
            return $requireAll;
        
        }

        foreach ($this->cachedRoles() as $role) {
            if ($role->name == $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the user has a role by its name.
     *
     * @param string|array $name       Role name or array of role names.
     * @param bool         $requireAll All roles in the array are required.
     *
     * @return bool
     */
    public function hasMandate($name, $requireAll = false)
    {
        if (is_array($name)) {
            if (empty($name)) {
                return true;
            }

            foreach ($name as $mandateName) {
                $hasMandate = $this->hasMandate($roleName);

                if ($hasMandate && !$requireAll) {
                    return true;
                } elseif (!$hasMandate && $requireAll) {
                    return false;
                }
            }

            // If we've made it this far and $requireAll is FALSE, then NONE of the roles were found
            // If we've made it this far and $requireAll is TRUE, then ALL of the roles were found.
            // Return the value of $requireAll;
            return $requireAll;
        
        }

        foreach ($this->cachedMandates() as $mandate) {
            if ($mandate->name == $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has a permission by its name.
     *
     * @param string|array $permission Permission string or array of permissions.
     * @param bool         $requireAll All permissions in the array are required.
     *
     * @return bool
     */
    public function can($permission, $requireAll = false)
    {
            if(!is_array($permission)){
                $permission = [$permission];
            }

            $availablePermissions = [];

            foreach ($this->cachedMandates() as $mandate) {
                
                $isPermitted = $mandate->hasPermission($permission,false,true);

                if($isPermitted && !$requireAll){
                    return true;
                }

                $availablePermissions = array_merge($availablePermissions,$isPermitted);

                if(!array_diff($permission,$availablePermissions)){
                    return true;
                }
            
            }
        
            foreach ($this->cachedRoles() as $role) {
               
                $isPermitted = $role->hasPermission($permission,false,true);

                if($isPermitted && !$requireAll){
                    return true;
                }

                $availablePermissions = array_merge($availablePermissions,$isPermitted);

                if(!array_diff($permission,$availablePermissions)){
                    return true;
                }
            
            }

            return (Boolean) !array_diff($permission,$availablePermissions);
    }


    /**
     * Checks role(s) and permission(s).
     *
     * @param string|array $roles       Array of roles or comma separated string
     * @param string|array $permissions Array of permissions or comma separated string.
     * @param array        $options     validate_all (true|false) or return_type (boolean|array|both)
     *
     * @throws \InvalidArgumentException
     *
     * @return array|bool
     */
    public function ability($roles, $permissions, $options = [])
    {
        // Convert string to array if that's what is passed in.
        if (!is_array($roles)) {
            $roles = explode(',', $roles);
        }
        if (!is_array($permissions)) {
            $permissions = explode(',', $permissions);
        }

        // Set up default values and validate options.
        if (!isset($options['validate_all'])) {
            $options['validate_all'] = false;
        } else {
            if ($options['validate_all'] !== true && $options['validate_all'] !== false) {
                throw new InvalidArgumentException();
            }
        }
        if (!isset($options['return_type'])) {
            $options['return_type'] = 'boolean';
        } else {
            if ($options['return_type'] != 'boolean' &&
                $options['return_type'] != 'array' &&
                $options['return_type'] != 'both') {
                throw new InvalidArgumentException();
            }
        }

        // Loop through roles and permissions and check each.
        $checkedRoles = [];
        $checkedPermissions = [];
        foreach ($roles as $role) {
            $checkedRoles[$role] = $this->hasRole($role);
        }
        foreach ($permissions as $permission) {
            $checkedPermissions[$permission] = $this->can($permission);
        }

        // If validate all and there is a false in either
        // Check that if validate all, then there should not be any false.
        // Check that if not validate all, there must be at least one true.
        if (($options['validate_all'] && !(in_array(false, $checkedRoles) || in_array(false, $checkedPermissions))) ||
            (!$options['validate_all'] && (in_array(true, $checkedRoles) || in_array(true, $checkedPermissions)))) {
            $validateAll = true;
        } else {
            $validateAll = false;
        }

        // Return based on option
        if ($options['return_type'] == 'boolean') {
            return $validateAll;
        } elseif ($options['return_type'] == 'array') {
            return ['roles' => $checkedRoles, 'permissions' => $checkedPermissions];
        } else {
            return [$validateAll, ['roles' => $checkedRoles, 'permissions' => $checkedPermissions]];
        }
    }

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param mixed $role
     * @return Illuminate\Database\Eloquent\Model
     */
    public function attachRole($role)
    {
        if (is_object($role)) {
            $role = $role->getKey();
        }

        if (is_array($role)) {
            $role = $role['id'];
        }

        $this->roles()->attach($role);
        $this->flushCache();

        return $this;
    }

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param mixed $role
     * @return Illuminate\Database\Eloquent\Model
     */
    public function detachRole($role)
    {
        if (is_object($role)) {
            $role = $role->getKey();
        }

        if (is_array($role)) {
            $role = $role['id'];
        }

        $this->roles()->detach($role);
        $this->flushCache();

        return $this;
    }

    /**
     * Attach multiple roles to a user
     *
     * @param mixed $roles
     * @return Illuminate\Database\Eloquent\Model
     */
    public function attachRoles($roles)
    {
        foreach ($roles as $role) {
            $this->attachRole($role);
        }

        return $this;
    }

    /**
     * Detach multiple roles from a user
     *
     * @param mixed $roles
     * @return Illuminate\Database\Eloquent\Model
     */
    public function detachRoles($roles = null)
    {
        if (!$roles) {
            $roles = $this->roles()->get();
        }
        
        foreach ($roles as $role) {
            $this->detachRole($role);
        }

        return $this;
    }

   /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param mixed $role
     * @return Illuminate\Database\Eloquent\Model
     */
    public function attachMandate($mandate,$expiry)
    {
        if (is_object($mandate)) {
            $role = $mandate->getKey();
        }

        if (is_array($mandate)) {
            $mandate = $mandate['id'];
        }

        $this->mandates()->attach($mandate,["expiry" => $expiry]);
        $this->flushCache();

        return $this;
    }

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param mixed $role
     * @return Illuminate\Database\Eloquent\Model
     */
    public function detachMandate($mandate)
    {
        if (is_object($mandate)) {
            $mandate = $mandate->getKey();
        }

        if (is_array($mandate)) {
            $mandate = $mandate['id'];
        }

        $this->mandates()->detach($role);
        $this->flushCache();

        return $this;
    }

    /**
     * Attach multiple roles to a user
     *
     * @param mixed $roles
     * @return Illuminate\Database\Eloquent\Model
     */
    public function attachMandates($mandates)
    {
        foreach ($mandates as $mandate) {
            $this->attachMandate($mandate);
        }

        return $this;
    }

    /**
     * Detach multiple roles from a user
     *
     * @param mixed $roles
     * @return Illuminate\Database\Eloquent\Model
     */
    public function detachMandates($mandates = null)
    {
        if (!$mandates) {
            $mandates = $this->mandates()->get();
        }
        
        foreach ($mandates as $mandate) {
            $this->detachMandate($mandate);
        }

        return $this;
    }

    /**
     * This scope allows to retrive users with an specific role
     * @param  Illuminate\Database\Eloquent\Builder $query
     * @param  string $role
     * @return Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereRoleIs($query, $role = '')
    {
        return $query->whereHas('roles', function ($roleQuery) use ($role) {
            $roleQuery->where('name', $role);
        });
    }

    /**
     * Flush the user's cache
     * @return void
     */
    public function flushCache()
    {
        Cache::forget('itctrust_roles_for_user_' . $this->getKey());
        Cache::forget('itctrust_mandates_for_user_' . $this->getKey());
    }
}
