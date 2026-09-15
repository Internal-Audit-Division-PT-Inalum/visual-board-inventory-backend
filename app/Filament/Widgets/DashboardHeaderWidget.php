<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class DashboardHeaderWidget extends Widget
{
    protected string $view = 'filament.widgets.dashboard-header';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -3; // Highest priority to show at top

    public function getUserName(): string
    {
        return Auth::user()?->name ?? 'User';
    }

    public function getRoleName(): string
    {
        $role = Auth::user()?->roles?->first()?->name ?? 'Guest';

        return ucwords(str_replace('_', ' ', $role));
    }
}
