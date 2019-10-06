<?php


namespace mttzzz\laravelTelegramLog;


use Illuminate\Support\ServiceProvider;

class TelegramLogServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config.php' => config_path('telegramLog.php'),
        ], 'config');
    }
}
