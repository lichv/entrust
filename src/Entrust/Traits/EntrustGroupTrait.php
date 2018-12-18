<?php
namespace Lichv\Entrust\Traits;

/**
 * This file is part of Entrust,
 * a group & permission management solution for Laravel.
 *
 * @license MIT
 * @package Lichv\Entrust
 */

use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;

trait EntrustGroupTrait
{

    /**
     * Boot the group model
     * Attach event listener to remove the many-to-many records when trying to delete
     * Will NOT delete any records if the group model uses soft deletes.
     *
     * @return void|bool
     */
    public static function boot()
    {
        parent::boot();

        static::deleting(function ($group) {
            if (!method_exists(Config::get('entrust.group'), 'bootSoftDeletes')) {
                $group->users()->sync([]);
                $group->roles()->sync([]);
            }

            return true;
        });
    }

    public function save(array $options = [])
    {   //both inserts and updates
        if (!parent::save($options)) {
            return false;
        }
        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(Config::get('entrust.role_group_table'))->flush();
            Cache::tags(Config::get('entrust.group_user_table'))->flush();
        }
        return true;
    }

    public function delete(array $options = [])
    {   //soft or hard
        if (!parent::delete($options)) {
            return false;
        }
        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(Config::get('entrust.role_group_table'))->flush();
            Cache::tags(Config::get('entrust.group_user_table'))->flush();
        }
        return true;
    }

    public function restore()
    {   //soft delete undo's
        if (!parent::restore()) {
            return false;
        }
        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(Config::get('entrust.role_group_table'))->flush();
            Cache::tags(Config::get('entrust.group_user_table'))->flush();
        }
        return true;
    }

    /**
     * Many-to-Many relations with the user model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(Config::get('auth.providers.users.model'), Config::get('entrust.group_user_table'), Config::get('entrust.group_foreign_key'), Config::get('entrust.user_foreign_key'));
    }

    /**
     * Many-to-Many relations with the permission model.
     * Named "perms" for backwards compatibility. Also because "perms" is short and sweet.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Config::get('entrust.role'), Config::get('entrust.role_group_table'), Config::get('entrust.group_foreign_key'), Config::get('entrust.role_foreign_key'));
    }

    //Big block of caching functionality.
    public function cachedRoles()
    {
        $groupPrimaryKey = $this->primaryKey;
        $cacheKey = 'entrust_roles_for_group_' . $this->$groupPrimaryKey;
        if (Cache::getStore() instanceof TaggableStore) {
            return Cache::tags(Config::get('entrust.role_group_table'))->remember($cacheKey, Config::get('cache.ttl', 60), function () {
                return $this->roles()->get();
            });
        } else return $this->roles()->get();
    }

    //Big block of caching functionality.
    public function cachedUsers()
    {
        $groupPrimaryKey = $this->primaryKey;
        $cacheKey = 'entrust_users_for_group_' . $this->$groupPrimaryKey;
        if (Cache::getStore() instanceof TaggableStore) {
            return Cache::tags(Config::get('entrust.group_user_table'))->remember($cacheKey, Config::get('cache.ttl', 60), function () {
                return $this->users()->get();
            });
        } else return $this->users()->get();
    }


    /**
     * Checks if the group has a role by its name.
     *
     * @param string|array $name Permission name or array of role names.
     * @param bool $requireAll All roles in the array are required.
     *
     * @return bool
     */
    public function hasRole($name, $requireAll = false)
    {
        if (is_array($name)) {
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
        } else {
            foreach ($this->cachedRoles() as $role) {
                if ($role->name == $name) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Checks if the group has a user by its name.
     *
     * @param string|array $name Permission name or array of user names.
     * @param bool $requireAll All users in the array are required.
     *
     * @return bool
     */
    public function hasUser($user, $requireAll = false)
    {
        if (is_array($user)) {
            foreach ($user as $id) {
                $hasUser = $this->hasUser($id);

                if ($hasUser && !$requireAll) {
                    return true;
                } elseif (!$hasUser && $requireAll) {
                    return false;
                }
            }

            // If we've made it this far and $requireAll is FALSE, then NONE of the users were found
            // If we've made it this far and $requireAll is TRUE, then ALL of the users were found.
            // Return the value of $requireAll;
            return $requireAll;
        } else {
            foreach ($this->cachedUsers() as $user) {
                if ($user->id == $user) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Save the inputted roles.
     *
     * @param mixed $inputRoles
     *
     * @return void
     */
    public function saveRoles($inputRoles)
    {
        if (!empty($inputRoles)) {
            $this->roles()->sync($inputRoles);
        } else {
            $this->roles()->detach();
        }

        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(Config::get('entrust.role_group_table'))->flush();
        }
    }

    /**
     * Save the inputted users.
     *
     * @param mixed $inputUsers
     *
     * @return void
     */
    public function saveUsers($inputUsers)
    {
        if (!empty($inputUsers)) {
            $this->users()->sync($inputUsers);
        } else {
            $this->users()->detach();
        }

        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(Config::get('entrust.group_user_table'))->flush();
        }
    }

    /**
     * Attach role to current group.
     *
     * @param object|array $role
     *
     * @return void
     */
    public function attachRole($role)
    {
        if (is_object($role)) {
            $role = $role->getKey();
        }

        if (is_array($role)) {
            return $this->attachRoles($role);
        }

        $this->roles()->attach($role);
    }

    /**
     * Attach user to current group.
     *
     * @param object|array $user
     *
     * @return void
     */
    public function attachUser($user)
    {
        if (is_object($user)) {
            $user = $user->getKey();
        }

        if (is_array($user)) {
            return $this->attachUsers($user);
        }

        $this->users()->attach($user);
    }

    /**
     * Detach role from current group.
     *
     * @param object|array $role
     *
     * @return void
     */
    public function detachRole($role)
    {
        if (is_object($role)) {
            $role = $role->getKey();
        }

        if (is_array($role)) {
            return $this->detachRoles($role);
        }

        $this->roles()->detach($role);
    }

    /**
     * Detach user from current group.
     *
     * @param object|array $user
     *
     * @return void
     */
    public function detachUser($user)
    {
        if (is_object($user)) {
            $user = $user->getKey();
        }

        if (is_array($user)) {
            return $this->detachUsers($user);
        }

        $this->users()->detach($user);
    }

    /**
     * Attach multiple roles to current group.
     *
     * @param mixed $roles
     *
     * @return void
     */
    public function attachRoles($roles)
    {
        foreach ($roles as $role) {
            $this->attachRole($role);
        }
    }

    /**
     * Attach multiple users to current group.
     *
     * @param mixed $users
     *
     * @return void
     */
    public function attachUsers($users)
    {
        foreach ($users as $user) {
            $this->attachUser($user);
        }
    }

    /**
     * Detach multiple roles from current group
     *
     * @param mixed $roles
     *
     * @return void
     */
    public function detachRoles($roles = null)
    {
        if (!$roles) $roles = $this->roles()->get();

        foreach ($roles as $role) {
            $this->detachRole($role);
        }
    }

    /**
     * Detach multiple users from current group
     *
     * @param mixed $users
     *
     * @return void
     */
    public function detachUsers($users = null)
    {
        if (!$users) $users = $this->users()->get();

        foreach ($users as $user) {
            $this->detachUser($user);
        }
    }
}
