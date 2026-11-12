<?php

declare(strict_types=1);

namespace App\Domains\VisualBoard\Policies;

use App\Domains\VisualBoard\Models\MasterWorkstationCriteria;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class MasterWorkstationCriteriaPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MasterWorkstationCriteria');
    }

    public function view(AuthUser $authUser, MasterWorkstationCriteria $masterWorkstationCriteria): bool
    {
        return $authUser->can('View:MasterWorkstationCriteria');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MasterWorkstationCriteria');
    }

    public function update(AuthUser $authUser, MasterWorkstationCriteria $masterWorkstationCriteria): bool
    {
        return $authUser->can('Update:MasterWorkstationCriteria');
    }

    public function delete(AuthUser $authUser, MasterWorkstationCriteria $masterWorkstationCriteria): bool
    {
        return $authUser->can('Delete:MasterWorkstationCriteria');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MasterWorkstationCriteria');
    }

    public function restore(AuthUser $authUser, MasterWorkstationCriteria $masterWorkstationCriteria): bool
    {
        return $authUser->can('Restore:MasterWorkstationCriteria');
    }

    public function forceDelete(AuthUser $authUser, MasterWorkstationCriteria $masterWorkstationCriteria): bool
    {
        return $authUser->can('ForceDelete:MasterWorkstationCriteria');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MasterWorkstationCriteria');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MasterWorkstationCriteria');
    }

    public function replicate(AuthUser $authUser, MasterWorkstationCriteria $masterWorkstationCriteria): bool
    {
        return $authUser->can('Replicate:MasterWorkstationCriteria');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MasterWorkstationCriteria');
    }
}
