<?php

declare(strict_types=1);

namespace App\Domains\Portal\Policies;

use App\Domains\Portal\Models\QuickLink;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class QuickLinkPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:QuickLink');
    }

    public function view(AuthUser $authUser, QuickLink $quickLink): bool
    {
        return $authUser->can('View:QuickLink');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:QuickLink');
    }

    public function update(AuthUser $authUser, QuickLink $quickLink): bool
    {
        return $authUser->can('Update:QuickLink');
    }

    public function delete(AuthUser $authUser, QuickLink $quickLink): bool
    {
        return $authUser->can('Delete:QuickLink');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:QuickLink');
    }

    public function restore(AuthUser $authUser, QuickLink $quickLink): bool
    {
        return $authUser->can('Restore:QuickLink');
    }

    public function forceDelete(AuthUser $authUser, QuickLink $quickLink): bool
    {
        return $authUser->can('ForceDelete:QuickLink');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:QuickLink');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:QuickLink');
    }

    public function replicate(AuthUser $authUser, QuickLink $quickLink): bool
    {
        return $authUser->can('Replicate:QuickLink');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:QuickLink');
    }
}
