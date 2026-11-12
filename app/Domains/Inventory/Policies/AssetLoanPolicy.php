<?php

declare(strict_types=1);

namespace App\Domains\Inventory\Policies;

use App\Domains\Inventory\Models\AssetLoan;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class AssetLoanPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AssetLoan');
    }

    public function view(AuthUser $authUser, AssetLoan $assetLoan): bool
    {
        return $authUser->can('View:AssetLoan');
    }

    public function create(AuthUser $authUser): bool
    {
        return false;
    }

    public function update(AuthUser $authUser, AssetLoan $assetLoan): bool
    {
        return false;
    }

    public function delete(AuthUser $authUser, AssetLoan $assetLoan): bool
    {
        return false;
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return false;
    }

    public function restore(AuthUser $authUser, AssetLoan $assetLoan): bool
    {
        return false;
    }

    public function forceDelete(AuthUser $authUser, AssetLoan $assetLoan): bool
    {
        return false;
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return false;
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return false;
    }

    public function replicate(AuthUser $authUser, AssetLoan $assetLoan): bool
    {
        return false;
    }

    public function reorder(AuthUser $authUser): bool
    {
        return false;
    }
}
