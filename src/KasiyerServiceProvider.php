<?php

namespace Frkcn\Kasiyer;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class KasiyerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerRoutes();
        $this->registerMigrations();
        $this->registerPublishing();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->configure();
    }

    /**
     * Setup the configuration for Kasiyer.
     *
     * @return void
     */
    protected function configure()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/kasiyer.php', 'kasiyer'
        );
    }

    /**
     * Boot the package routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        Route::group([
            'prefix' => config('kasiyer.path'),
            'namespace' => 'Frkcn\Kasiyer\Http\Controllers',
            'as' => 'kasiyer.',
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });
    }

    /**
     * Register the package migrations.
     *
     * @return void
     */
    protected function registerMigrations()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/kasiyer.php' => $this->app->configPath('kasiyer.php'),
            ], 'kasiyer-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => $this->app->databasePath('migrations'),
            ], 'kasiyer-migrations');
        }
    }
}
