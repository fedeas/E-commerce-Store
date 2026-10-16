<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->withCommands([
        \App\Console\Commands\ImportCatalogProductsFromExcel::class,
        \App\Console\Commands\ImportCatalogCategoriesFromExcel::class,
        \App\Console\Commands\SyncSupplierProducts::class,
        \App\Console\Commands\ScanSupplierCategories::class,
        \App\Console\Commands\ClearCatalogProducts::class,
    ])
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('supplier:scan-categories all')
            ->hourlyAt(0)
            ->withoutOverlapping()
            ->runInBackground();

        $schedule->command('supplier:sync-products all')
            ->hourlyAt(10)
            ->withoutOverlapping()
            ->runInBackground();

        $schedule->command('supplier:sync-products all --product-data --sync-images')
            ->dailyAt('02:15')
            ->withoutOverlapping()
            ->runInBackground();
    })
    ->create();
