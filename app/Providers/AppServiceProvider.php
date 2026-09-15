<?php

namespace App\Providers;

use App\Domains\VisualBoard\Models\Abnormality;
use App\Domains\VisualBoard\Observers\AbnormalityObserver;
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
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Visual Board domain
        $this->app->bind(MonthlyScheduleRepositoryInterface::class, MonthlyScheduleRepository::class);
        $this->app->bind(AbnormalityRepositoryInterface::class, AbnormalityRepository::class);
        $this->app->bind(ZoneRepositoryInterface::class, ZoneRepository::class);
        $this->app->bind(SchedulePicRepositoryInterface::class, SchedulePicRepository::class);
        $this->app->bind(WorkstationRepositoryInterface::class, WorkstationRepository::class);

        // Inventory domain
        $this->app->bind(LocationRepositoryInterface::class, LocationRepository::class);
        $this->app->bind(ItemRepositoryInterface::class, ItemRepository::class);

        // HR domain
        $this->app->bind(DepartmentRepositoryInterface::class, DepartmentRepository::class);
        $this->app->bind(EmployeeRepositoryInterface::class, EmployeeRepository::class);
        $this->app->bind(EmployeeAttendanceRepositoryInterface::class, EmployeeAttendanceRepository::class);

        // Portal domain
        $this->app->bind(BulletinRepositoryInterface::class, BulletinRepository::class);
    }

    public function boot(): void
    {
        Abnormality::observe(AbnormalityObserver::class);

        Factory::guessFactoryNamesUsing(function (string $modelName) {
            $modelName = class_basename($modelName);

            return 'Database\\Factories\\' . $modelName . 'Factory';
        });
    }
}
