<?php

namespace App\Domains\Core\Models;

use App\Domains\HR\Models\Employee;
use App\Shared\Concerns\HasUlid;
use DomainException;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property-read Employee|null $employee
 */
class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasRoles, HasUlid, Notifiable {
        HasRoles::bootHasRoles as spatieBootHasRoles;
        HasRoles::removeRole as spatieRemoveRole;
        HasRoles::syncRoles as spatieSyncRoles;
    }

    public static function bootHasRoles(): void
    {
        static::deleting(function (User $user): void {
            if ($user->isLastStandingSuperAdmin()) {
                throw new DomainException('Operasi ditolak. Sistem wajib memiliki minimal satu akun Super Admin aktif.');
            }
        });

        static::spatieBootHasRoles();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return ! str_ends_with($this->email, '@synthetic.inalum.id');
    }

    public function isLastStandingSuperAdmin(): bool
    {
        if (! $this->exists || ! $this->hasRole('super_admin')) {
            return false;
        }

        return static::role('super_admin')->count() <= 1;
    }

    /**
     * @param  string|int|array<mixed>|\Spatie\Permission\Contracts\Role|Collection<int, mixed>|\BackedEnum  ...$role
     */
    public function removeRole(...$role): static
    {
        $targetRoles = collect($role)->flatten();
        $isRemovingSuperAdmin = $targetRoles->contains(function ($item): bool {
            if ($item instanceof \Spatie\Permission\Contracts\Role) {
                return $item->name === 'super_admin';
            }

            if (is_int($item) || is_string($item)) {
                $superAdminRole = Role::where('name', 'super_admin')->first();

                return $item === 'super_admin' || ($superAdminRole !== null && (string) $superAdminRole->getKey() === (string) $item);
            }

            return false;
        });

        if ($isRemovingSuperAdmin && $this->isLastStandingSuperAdmin()) {
            throw new DomainException('Operasi ditolak. Sistem wajib memiliki minimal satu akun Super Admin aktif.');
        }

        return $this->spatieRemoveRole(...$role);
    }

    /**
     * @param  string|int|array<mixed>|\Spatie\Permission\Contracts\Role|Collection<int, mixed>|\BackedEnum  ...$roles
     */
    public function syncRoles(...$roles): static
    {
        if ($this->exists && $this->isLastStandingSuperAdmin()) {
            $superAdminRole = Role::where('name', 'super_admin')->first();
            $incomingRoles = collect($roles)->flatten();

            $retainsSuperAdmin = $incomingRoles->contains(function ($item) use ($superAdminRole): bool {
                if ($item instanceof \Spatie\Permission\Contracts\Role) {
                    return $item->name === 'super_admin';
                }

                return $item === 'super_admin'
                    || ($superAdminRole !== null && (string) $superAdminRole->getKey() === (string) $item);
            });

            if (! $retainsSuperAdmin) {
                throw new DomainException('Operasi ditolak. Sistem wajib memiliki minimal satu akun Super Admin aktif.');
            }
        }

        return $this->spatieSyncRoles(...$roles);
    }

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * @return HasOne<Employee, $this>
     */
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }
}
