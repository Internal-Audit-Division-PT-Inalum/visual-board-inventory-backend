<?php

declare(strict_types=1);

namespace App\Domains\VisualBoard\Policies;

use App\Domains\VisualBoard\Models\FiveREvaluation;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class FiveREvaluationPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:FiveREvaluation');
    }

    public function view(AuthUser $authUser, FiveREvaluation $fiveREvaluation): bool
    {
        return $authUser->can('View:FiveREvaluation');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:FiveREvaluation');
    }

    public function update(AuthUser $authUser, FiveREvaluation $fiveREvaluation): bool
    {
        return $authUser->can('Update:FiveREvaluation');
    }

    public function delete(AuthUser $authUser, FiveREvaluation $fiveREvaluation): bool
    {
        return $authUser->can('Delete:FiveREvaluation');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:FiveREvaluation');
    }

    public function restore(AuthUser $authUser, FiveREvaluation $fiveREvaluation): bool
    {
        return $authUser->can('Restore:FiveREvaluation');
    }

    public function forceDelete(AuthUser $authUser, FiveREvaluation $fiveREvaluation): bool
    {
        return $authUser->can('ForceDelete:FiveREvaluation');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:FiveREvaluation');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:FiveREvaluation');
    }

    public function replicate(AuthUser $authUser, FiveREvaluation $fiveREvaluation): bool
    {
        return $authUser->can('Replicate:FiveREvaluation');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:FiveREvaluation');
    }
}
