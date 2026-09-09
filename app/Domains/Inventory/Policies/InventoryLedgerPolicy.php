<?php

declare(strict_types=1);

namespace App\Domains\Inventory\Policies;

use App\Domains\Inventory\Models\InventoryLedger;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class InventoryLedgerPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:InventoryLedger');
    }

    public function view(AuthUser $authUser, InventoryLedger $inventoryLedger): bool
    {
        return $authUser->can('View:InventoryLedger');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:InventoryLedger');
    }

    public function update(AuthUser $authUser, InventoryLedger $inventoryLedger): bool
    {
        return $authUser->can('Update:InventoryLedger');
    }

    public function delete(AuthUser $authUser, InventoryLedger $inventoryLedger): bool
    {
        return $authUser->can('Delete:InventoryLedger');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:InventoryLedger');
    }

    public function restore(AuthUser $authUser, InventoryLedger $inventoryLedger): bool
    {
        return $authUser->can('Restore:InventoryLedger');
    }

    public function forceDelete(AuthUser $authUser, InventoryLedger $inventoryLedger): bool
    {
        return $authUser->can('ForceDelete:InventoryLedger');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:InventoryLedger');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:InventoryLedger');
    }

    public function replicate(AuthUser $authUser, InventoryLedger $inventoryLedger): bool
    {
        return $authUser->can('Replicate:InventoryLedger');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:InventoryLedger');
    }
}
