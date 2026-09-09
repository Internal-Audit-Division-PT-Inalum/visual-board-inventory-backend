<?php

declare(strict_types=1);

namespace App\Domains\VisualBoard\Policies;

use App\Domains\VisualBoard\Models\InspectionCriteria;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class InspectionCriteriaPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:InspectionCriteria');
    }

    public function view(AuthUser $authUser, InspectionCriteria $inspectionCriteria): bool
    {
        return $authUser->can('View:InspectionCriteria');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:InspectionCriteria');
    }

    public function update(AuthUser $authUser, InspectionCriteria $inspectionCriteria): bool
    {
        return $authUser->can('Update:InspectionCriteria');
    }

    public function delete(AuthUser $authUser, InspectionCriteria $inspectionCriteria): bool
    {
        return $authUser->can('Delete:InspectionCriteria');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:InspectionCriteria');
    }

    public function restore(AuthUser $authUser, InspectionCriteria $inspectionCriteria): bool
    {
        return $authUser->can('Restore:InspectionCriteria');
    }

    public function forceDelete(AuthUser $authUser, InspectionCriteria $inspectionCriteria): bool
    {
        return $authUser->can('ForceDelete:InspectionCriteria');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:InspectionCriteria');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:InspectionCriteria');
    }

    public function replicate(AuthUser $authUser, InspectionCriteria $inspectionCriteria): bool
    {
        return $authUser->can('Replicate:InspectionCriteria');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:InspectionCriteria');
    }
}
