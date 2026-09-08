<?php

namespace App\Providers;

use App\Repositories\Contracts\MonthlyScheduleRepositoryInterface;
use App\Repositories\Eloquent\MonthlyScheduleRepository;
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
    }

    public function boot(): void
    {
        Factory::guessFactoryNamesUsing(function (string $modelName) {
            $modelName = class_basename($modelName);

            return 'Database\\Factories\\' . $modelName . 'Factory';
        });
    }
}
