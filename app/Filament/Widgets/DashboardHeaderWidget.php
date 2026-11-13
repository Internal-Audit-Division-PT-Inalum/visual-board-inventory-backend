<?php

namespace App\Filament\Widgets;

use App\Domains\Core\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class DashboardHeaderWidget extends Widget
{
    protected string $view = 'filament.widgets.dashboard-header';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -4; // Highest priority to show at top

    public function getUserName(): string
    {
        $user = Auth::user();

        return $user instanceof User ? $user->name : 'User';
    }

    public function getRoleName(): string
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            return 'Guest';
        }

        $role = $user->getRoleNames()->first();

        return ucwords(str_replace('_', ' ', is_string($role) ? $role : 'Guest'));
    }
}
