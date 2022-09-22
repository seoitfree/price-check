<?php

namespace ItFreeCrm\Hotline\Infrastructure\Laravel\Provider;


use Illuminate\Support\ServiceProvider;
use ItFreeCrm\Hotline\UI\Console\AddReportConsole;

class HotlineProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../../../UI/Http/Routes/api.php');

        $this->loadMigrationsFrom(__DIR__ . '/../../../Infrastructure/Laravel/Migrations');

        //$this->loadTranslationsFrom(__DIR__ . '/../../../Infrastructure/Laravel/Lang', 'hotline');

        if ($this->app->runningInConsole()) {
            $this->commands([
                AddReportConsole::class
            ]);
        }

    }
}
