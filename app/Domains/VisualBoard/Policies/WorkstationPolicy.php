<?php

declare(strict_types=1);

namespace App\Domains\VisualBoard\Policies;

use App\Domains\VisualBoard\Models\Workstation;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class WorkstationPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Workstation');
    }

    public function view(AuthUser $authUser, Workstation $workstation): bool
    {
        return $authUser->can('View:Workstation');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Workstation');
    }

    public function update(AuthUser $authUser, Workstation $workstation): bool
    {
        return $authUser->can('Update:Workstation');
    }

    public function delete(AuthUser $authUser, Workstation $workstation): bool
    {
        return $authUser->can('Delete:Workstation');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Workstation');
    }

    public function restore(AuthUser $authUser, Workstation $workstation): bool
    {
        return $authUser->can('Restore:Workstation');
    }

    public function forceDelete(AuthUser $authUser, Workstation $workstation): bool
    {
        return $authUser->can('ForceDelete:Workstation');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Workstation');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Workstation');
    }

    public function replicate(AuthUser $authUser, Workstation $workstation): bool
    {
        return $authUser->can('Replicate:Workstation');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Workstation');
    }
}
