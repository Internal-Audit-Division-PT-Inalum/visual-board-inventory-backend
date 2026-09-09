<?php

declare(strict_types=1);

namespace App\Domains\VisualBoard\Policies;

use App\Domains\VisualBoard\Models\MonthlySchedule;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class MonthlySchedulePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MonthlySchedule');
    }

    public function view(AuthUser $authUser, MonthlySchedule $monthlySchedule): bool
    {
        return $authUser->can('View:MonthlySchedule');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MonthlySchedule');
    }

    public function update(AuthUser $authUser, MonthlySchedule $monthlySchedule): bool
    {
        return $authUser->can('Update:MonthlySchedule');
    }

    public function delete(AuthUser $authUser, MonthlySchedule $monthlySchedule): bool
    {
        return $authUser->can('Delete:MonthlySchedule');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MonthlySchedule');
    }

    public function restore(AuthUser $authUser, MonthlySchedule $monthlySchedule): bool
    {
        return $authUser->can('Restore:MonthlySchedule');
    }

    public function forceDelete(AuthUser $authUser, MonthlySchedule $monthlySchedule): bool
    {
        return $authUser->can('ForceDelete:MonthlySchedule');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MonthlySchedule');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MonthlySchedule');
    }

    public function replicate(AuthUser $authUser, MonthlySchedule $monthlySchedule): bool
    {
        return $authUser->can('Replicate:MonthlySchedule');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MonthlySchedule');
    }
}
