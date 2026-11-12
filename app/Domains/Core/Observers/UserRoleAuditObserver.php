<?php

declare(strict_types=1);

namespace App\Domains\Core\Observers;

use App\Domains\Core\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Events\RoleAttachedEvent;
use Spatie\Permission\Events\RoleDetachedEvent;
use Spatie\Permission\Models\Role;

class UserRoleAuditObserver
{
    public function handleRoleAttached(RoleAttachedEvent $event): void
    {
        if (! $event->model instanceof User) {
            return;
        }

        $roleNames = $this->resolveRoleNames($event->rolesOrIds);
        $currentRoles = $event->model->roles()->pluck('name')->values()->all();
        $rolesBefore = array_values(array_diff($currentRoles, $roleNames));

        $this->logAudit($event->model, 'user.role_attached', $rolesBefore, $currentRoles, $roleNames);
    }

    public function handleRoleDetached(RoleDetachedEvent $event): void
    {
        if (! $event->model instanceof User) {
            return;
        }

        $roleNames = $this->resolveRoleNames($event->rolesOrIds);
        $currentRoles = $event->model->roles()->pluck('name')->values()->all();
        $rolesBefore = array_values(array_unique(array_merge($currentRoles, $roleNames)));

        $this->logAudit($event->model, 'user.role_detached', $rolesBefore, $currentRoles, $roleNames);
    }

    /**
     * @param  array<int, string>  $rolesBefore
     * @param  array<int, string>  $rolesAfter
     * @param  array<int, string>  $mutatedRoles
     */
    protected function logAudit(User $targetUser, string $eventType, array $rolesBefore, array $rolesAfter, array $mutatedRoles): void
    {
        /** @var User|null $actor */
        $actor = auth()->user();

        Log::info('Security Audit: User roles modified', [
            'event' => $eventType,
            'actor_id' => $actor?->id,
            'actor_email' => $actor?->email,
            'target_user_id' => $targetUser->id,
            'target_user_email' => $targetUser->email,
            'mutated_roles' => $mutatedRoles,
            'roles_before' => $rolesBefore,
            'roles_after' => $rolesAfter,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * @return array<int, string>
     */
    protected function resolveRoleNames(mixed $rolesOrIds): array
    {
        $items = $rolesOrIds instanceof Collection ? $rolesOrIds->all() : (array) $rolesOrIds;
        $names = [];

        foreach ($items as $item) {
            if ($item instanceof RoleContract) {
                $names[] = (string) $item->name;

                continue;
            }

            if (is_int($item) || (is_string($item) && is_numeric($item))) {
                $role = Role::find((int) $item);
                if ($role !== null) {
                    $names[] = (string) $role->name;
                }

                continue;
            }

            if (is_string($item)) {
                $names[] = $item;
            }
        }

        return array_values(array_unique($names));
    }
}
