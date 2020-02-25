<?php

namespace mttzzz\laravelTelegramLog;

use Monolog\Handler\Handler;

class HandlerTelegramLogger extends Handler
{
    public function isHandling(array $record): bool
    {
        return true;
    }

    public function handle(array $record): bool
    {
        Telegram::log([
            'message' => $record['message'],
            'linkLog' => env('APP_URL').'/'.config('telegramLog.url','logs')
        ]);
        return true;
    }
}
{

}