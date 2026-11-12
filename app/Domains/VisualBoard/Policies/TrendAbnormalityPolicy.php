<?php

declare(strict_types=1);

namespace App\Domains\VisualBoard\Policies;

use App\Domains\VisualBoard\Models\TrendAbnormality;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class TrendAbnormalityPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TrendAbnormality');
    }

    public function view(AuthUser $authUser, TrendAbnormality $trendAbnormality): bool
    {
        return $authUser->can('View:TrendAbnormality');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TrendAbnormality');
    }

    public function update(AuthUser $authUser, TrendAbnormality $trendAbnormality): bool
    {
        return $authUser->can('Update:TrendAbnormality');
    }

    public function delete(AuthUser $authUser, TrendAbnormality $trendAbnormality): bool
    {
        return $authUser->can('Delete:TrendAbnormality');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:TrendAbnormality');
    }

    public function restore(AuthUser $authUser, TrendAbnormality $trendAbnormality): bool
    {
        return $authUser->can('Restore:TrendAbnormality');
    }

    public function forceDelete(AuthUser $authUser, TrendAbnormality $trendAbnormality): bool
    {
        return $authUser->can('ForceDelete:TrendAbnormality');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TrendAbnormality');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TrendAbnormality');
    }

    public function replicate(AuthUser $authUser, TrendAbnormality $trendAbnormality): bool
    {
        return $authUser->can('Replicate:TrendAbnormality');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TrendAbnormality');
    }
}
