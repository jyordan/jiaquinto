<?php

use App\Models\AppSetting;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('app:cliniko-monitor-command')
            ->everyFiveMinutes()
            ->when(function () {
                return AppSetting::where('key', 'phase-2')->value('value') != 1;
            });

        $schedule->command('app:ghl-conversion-command')
            ->everyFiveMinutes()
            ->when(function () {
                return AppSetting::where('key', 'phase-2')->value('value') == 1;
            });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
