<?php

namespace mttzzz\laravelTelegramLog;

use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Telegram
{
    public static function log($message)
    {
        switch ($message) {
            case $message instanceof Exception :
                $message = ['message' => $message->getMessage()];
                break;
            case $message instanceof RequestException :
                $message = json_decode($message->response->body(), 1);
                break;
            case is_array($message) :
                foreach ($message as $key => $item) {
                    $message[$key] = (array)$item;
                }
                break;
            case is_object($message) :
                $message = (array)$message;
                break;
            case self::isJson($message) :
                $message = json_decode($message, 1);
                break;
            default:
                $message = ['message' => $message];
        }
        try {
            self::send($message);
        } catch (Exception $e) {
            sleep(1);
            Telegram::log('упал');
        }
    }

    private static function isJson($string)
    {
        json_decode($string);
        return json_last_error() == JSON_ERROR_NONE;
    }

    private static function send(array $message)
    {
        if (!is_array($message)) {
            $text = 'НЕ МАССИВ!';
        } else {
            $text = '<b>' . env('APP_NAME') . '</b>' . PHP_EOL
                . '<b>' . env('APP_ENV') . '</b>' . PHP_EOL
                . '<i>Message:</i>' . PHP_EOL
                . '<code>' . json_encode($message, 64 | 128 | 256) . '</code>';
        }

        $query = [
            'chat_id' => config('laraveltelegramlog.chat_id'),
            'text' => $text,
            'parse_mode' => 'html',
        ];
        if (config('sentry.dsn')) {
            $url = 'https://sentry.io/organizations/pushka/issues/?project=' . Str::afterLast(config('sentry.dsn'), '/');
            $keyboard = ["inline_keyboard" => [[[
                "text" => 'Перейти в sentry',
                "url" => $url
            ]]]];
            $query['reply_markup'] = json_encode($keyboard);
        }

        if (mb_strlen($text) < 4096) {
            Http::get('https://api.telegram.org/bot' . config('laraveltelegramlog.token') . '/sendMessage', $query)->throw();
        } else {
            Http::asMultipart()->attach('document', $text, env('APP_NAME') . '.txt')
                ->post('https://api.telegram.org/bot' . config('laraveltelegramlog.token') . '/sendDocument', $query)->throw();
        }
    }
}
