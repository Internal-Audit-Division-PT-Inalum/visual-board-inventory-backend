<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domains\Core\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $authUser): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function view(User $authUser, Role $role): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function create(User $authUser): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function update(User $authUser, Role $role): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function delete(User $authUser, Role $role): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function deleteAny(User $authUser): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function restore(User $authUser, Role $role): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function forceDelete(User $authUser, Role $role): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function forceDeleteAny(User $authUser): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function restoreAny(User $authUser): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function replicate(User $authUser, Role $role): bool
    {
        return $authUser->hasRole('super_admin');
    }

    public function reorder(User $authUser): bool
    {
        return $authUser->hasRole('super_admin');
    }
}
