<?php

namespace mttzzz\LaravelTelegramLog;

use Illuminate\Support\ServiceProvider;

class LaravelTelegramLogServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'mttzzz');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'mttzzz');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laraveltelegramlog.php', 'laraveltelegramlog');

        // Register the service the package provides.
        $this->app->singleton('laraveltelegramlog', function ($app) {
            return new LaravelTelegramLog;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['laraveltelegramlog'];
    }
    
    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/laraveltelegramlog.php' => config_path('laraveltelegramlog.php'),
        ], 'laraveltelegramlog.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/mttzzz'),
        ], 'laraveltelegramlog.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/mttzzz'),
        ], 'laraveltelegramlog.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/mttzzz'),
        ], 'laraveltelegramlog.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
