<?php
namespace Lichv\Entrust\Contracts;

/**
 * This file is part of Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Lichv\Entrust
 */

interface EntrustGroupInterface
{
    /**
     * Many-to-Many relations with Role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles();

    /**
     * Many-to-Many relations with Group.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users();

    /**
     * Checks if the user has a role by its name.
     *
     * @param string|array $name       Role name or array of role names.
     * @param bool         $requireAll All roles in the array are required.
     *
     * @return bool
     */
    public function hasRole($name, $requireAll = false);

    /**
     * Checks if the user has a role by its name.
     *
     * @param interge|array $name       Role name or array of role names.
     * @param bool         $requireAll All roles in the array are required.
     *
     * @return bool
     */
    public function hasUser($user, $requireAll = false);

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param mixed $role
     */
    public function attachRole($role);

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param mixed $role
     */
    public function attachUser($user);

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param mixed $role
     */
    public function detachRole($role);

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param mixed $role
     */
    public function detachUser($user);

    /**
     * Attach multiple roles to a user
     *
     * @param mixed $roles
     */
    public function attachRoles($roles);

    /**
     * Attach multiple roles to a user
     *
     * @param mixed $roles
     */
    public function attachUsers($users);

    /**
     * Detach multiple roles from a user
     *
     * @param mixed $roles
     */
    public function detachRoles($roles);

    /**
     * Detach multiple roles from a user
     *
     * @param mixed $roles
     */
    public function detachUsers($users);
}
