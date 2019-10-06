<?php


namespace mttzzz\laravelTelegramLog;


class TelegramLogServiceProvider
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
        ]);
    }
}
