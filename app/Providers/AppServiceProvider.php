<?php

namespace App\Providers;

use App\Repositories\Contracts\AbnormalityRepositoryInterface;
use App\Repositories\Contracts\MonthlyScheduleRepositoryInterface;
use App\Repositories\Contracts\ZoneRepositoryInterface;
use App\Repositories\Eloquent\AbnormalityRepository;
use App\Repositories\Eloquent\MonthlyScheduleRepository;
use App\Repositories\Eloquent\ZoneRepository;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            MonthlyScheduleRepositoryInterface::class,
            MonthlyScheduleRepository::class
        );
        $this->app->bind(AbnormalityRepositoryInterface::class, AbnormalityRepository::class);
        $this->app->bind(ZoneRepositoryInterface::class, ZoneRepository::class);
    }

    public function boot(): void
    {
        Factory::guessFactoryNamesUsing(function (string $modelName) {
            $modelName = class_basename($modelName);

            return 'Database\\Factories\\' . $modelName . 'Factory';
        });
    }
}
