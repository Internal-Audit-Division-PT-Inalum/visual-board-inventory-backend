<?php

namespace App\Providers;

use App\Domains\Core\Observers\UserRoleAuditObserver;
use App\Domains\Inventory\Models\Item;
use App\Domains\Inventory\Models\Location;
use App\Domains\VisualBoard\Models\Abnormality;
use App\Domains\VisualBoard\Observers\AbnormalityObserver;
use App\Observers\ItemObserver;
use App\Observers\LocationObserver;
use App\Repositories\Contracts\AbnormalityRepositoryInterface;
use App\Repositories\Contracts\BulletinRepositoryInterface;
use App\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Repositories\Contracts\EmployeeAttendanceRepositoryInterface;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use App\Repositories\Contracts\ItemRepositoryInterface;
use App\Repositories\Contracts\LocationRepositoryInterface;
use App\Repositories\Contracts\MonthlyScheduleRepositoryInterface;
use App\Repositories\Contracts\SchedulePicRepositoryInterface;
use App\Repositories\Contracts\WorkstationRepositoryInterface;
use App\Repositories\Contracts\ZoneRepositoryInterface;
use App\Repositories\Eloquent\AbnormalityRepository;
use App\Repositories\Eloquent\BulletinRepository;
use App\Repositories\Eloquent\DepartmentRepository;
use App\Repositories\Eloquent\EmployeeAttendanceRepository;
use App\Repositories\Eloquent\EmployeeRepository;
use App\Repositories\Eloquent\ItemRepository;
use App\Repositories\Eloquent\LocationRepository;
use App\Repositories\Eloquent\MonthlyScheduleRepository;
use App\Repositories\Eloquent\SchedulePicRepository;
use App\Repositories\Eloquent\WorkstationRepository;
use App\Repositories\Eloquent\ZoneRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Events\RoleAttachedEvent;
use Spatie\Permission\Events\RoleDetachedEvent;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MonthlyScheduleRepositoryInterface::class, MonthlyScheduleRepository::class);
        $this->app->bind(AbnormalityRepositoryInterface::class, AbnormalityRepository::class);
        $this->app->bind(ZoneRepositoryInterface::class, ZoneRepository::class);
        $this->app->bind(SchedulePicRepositoryInterface::class, SchedulePicRepository::class);
        $this->app->bind(WorkstationRepositoryInterface::class, WorkstationRepository::class);

        $this->app->bind(LocationRepositoryInterface::class, LocationRepository::class);
        $this->app->bind(ItemRepositoryInterface::class, ItemRepository::class);

        $this->app->bind(DepartmentRepositoryInterface::class, DepartmentRepository::class);
        $this->app->bind(EmployeeRepositoryInterface::class, EmployeeRepository::class);
        $this->app->bind(EmployeeAttendanceRepositoryInterface::class, EmployeeAttendanceRepository::class);

        $this->app->bind(BulletinRepositoryInterface::class, BulletinRepository::class);
    }

    public function boot(): void
    {
        $this->configureRuntimeHardening();
        $this->configureRateLimiters();

        Abnormality::observe(AbnormalityObserver::class);

        Item::observe(ItemObserver::class);
        Location::observe(LocationObserver::class);

        Event::listen(RoleAttachedEvent::class, [UserRoleAuditObserver::class, 'handleRoleAttached']);
        Event::listen(RoleDetachedEvent::class, [UserRoleAuditObserver::class, 'handleRoleDetached']);

        $clearKioskCache = function () {
            Cache::forget('inventory:kiosk:locations');
        };
        Media::created($clearKioskCache);
        Media::updated($clearKioskCache);
        Media::deleted($clearKioskCache);

        Factory::guessFactoryNamesUsing(function (string $modelName) {
            $modelName = class_basename($modelName);

            return 'Database\\Factories\\' . $modelName . 'Factory';
        });
    }

    private function configureRuntimeHardening(): void
    {
        $strict = ! app()->isProduction();
        Model::preventLazyLoading($strict);
        Model::preventSilentlyDiscardingAttributes($strict);
        Model::preventAccessingMissingAttributes($strict);

        $trustedProxies = config('security.trusted_proxies', ['127.0.0.1', '::1']);
        if ((is_array($trustedProxies) && $trustedProxies !== []) || (is_string($trustedProxies) && $trustedProxies !== '')) {
            TrustProxies::at($trustedProxies);
        }

        if ((bool) config('security.force_https', false)
            || (app()->environment('production') && str_starts_with((string) config('app.url', ''), 'https://'))) {
            URL::forceScheme('https');
        }
    }

    private function configureRateLimiters(): void
    {
        RateLimiter::for('kiosk-scan', function (Request $request): array {
            $ip = (string) ($request->ip() ?? 'unknown');
            $namecode = strtoupper(trim((string) $request->input('namecode', '')));
            $identityKey = $namecode !== '' ? "{$ip}|{$namecode}" : $ip;

            return [
                Limit::perMinute(180)->by("kiosk-scan-ip:{$ip}"),
                Limit::perMinute(6)->by("kiosk-scan-id:{$identityKey}"),
            ];
        });

        RateLimiter::for('kiosk-read', function (Request $request): Limit {
            $ip = (string) ($request->ip() ?? 'unknown');

            return Limit::perMinute(120)->by("kiosk-read:{$ip}");
        });

        RateLimiter::for('api-authenticated', function (Request $request): Limit {
            $identifier = $request->user()?->getAuthIdentifier();
            $key = $identifier !== null ? (string) $identifier : (string) ($request->ip() ?? 'unknown');

            return Limit::perMinute(180)->by("api-auth:{$key}");
        });

        RateLimiter::for('pin-change', function (Request $request): Limit {
            $identifier = $request->user()?->getAuthIdentifier();
            $key = $identifier !== null ? (string) $identifier : (string) ($request->ip() ?? 'unknown');

            return Limit::perMinute(10)->by("pin-change:{$key}");
        });
    }
}
