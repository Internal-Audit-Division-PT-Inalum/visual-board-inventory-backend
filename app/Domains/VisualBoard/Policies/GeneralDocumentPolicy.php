<?php

declare(strict_types=1);

namespace App\Domains\VisualBoard\Policies;

use App\Domains\VisualBoard\Models\GeneralDocument;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class GeneralDocumentPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:GeneralDocument');
    }

    public function view(AuthUser $authUser, GeneralDocument $generalDocument): bool
    {
        return $authUser->can('View:GeneralDocument');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:GeneralDocument');
    }

    public function update(AuthUser $authUser, GeneralDocument $generalDocument): bool
    {
        return $authUser->can('Update:GeneralDocument');
    }

    public function delete(AuthUser $authUser, GeneralDocument $generalDocument): bool
    {
        return $authUser->can('Delete:GeneralDocument');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:GeneralDocument');
    }

    public function restore(AuthUser $authUser, GeneralDocument $generalDocument): bool
    {
        return $authUser->can('Restore:GeneralDocument');
    }

    public function forceDelete(AuthUser $authUser, GeneralDocument $generalDocument): bool
    {
        return $authUser->can('ForceDelete:GeneralDocument');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:GeneralDocument');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:GeneralDocument');
    }

    public function replicate(AuthUser $authUser, GeneralDocument $generalDocument): bool
    {
        return $authUser->can('Replicate:GeneralDocument');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:GeneralDocument');
    }
}
