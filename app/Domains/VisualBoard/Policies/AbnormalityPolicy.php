<?php

declare(strict_types=1);

namespace App\Domains\VisualBoard\Policies;

use App\Domains\VisualBoard\Models\Abnormality;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class AbnormalityPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Abnormality');
    }

    public function view(AuthUser $authUser, Abnormality $abnormality): bool
    {
        return $authUser->can('View:Abnormality');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Abnormality');
    }

    public function update(AuthUser $authUser, Abnormality $abnormality): bool
    {
        return $authUser->can('Update:Abnormality');
    }

    public function delete(AuthUser $authUser, Abnormality $abnormality): bool
    {
        return $authUser->can('Delete:Abnormality');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Abnormality');
    }

    public function restore(AuthUser $authUser, Abnormality $abnormality): bool
    {
        return $authUser->can('Restore:Abnormality');
    }

    public function forceDelete(AuthUser $authUser, Abnormality $abnormality): bool
    {
        return $authUser->can('ForceDelete:Abnormality');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Abnormality');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Abnormality');
    }

    public function replicate(AuthUser $authUser, Abnormality $abnormality): bool
    {
        return $authUser->can('Replicate:Abnormality');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Abnormality');
    }
}
