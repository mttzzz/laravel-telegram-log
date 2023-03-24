<?php

namespace mttzzz\LaravelTelegramLog;

use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use function Sentry\captureException;
use TypeError;

class Telegram
{
    public static function log($message)
    {
        $message = match ($message) {
            $message instanceof Exception => $message->getMessage(),
            $message instanceof RequestException => self::handleRequestException($message),
            $message instanceof Collection => $message->toArray(),
            default => $message
        };

        $parsedMessage = match (gettype($message)) {
            "array", "object"=> (array)$message,
            default => ['message' => (string)$message]
        };

        try {
            if (gettype($message) !== 'array') {
                $message = ['message' => print_r($message, true)];
            }
            self::send($message);
        } 
        catch (Exception $e) {
            captureException($e);
        }
    }

    public static function handleRequestException(RequestException $exception)
    {
        $data = $exception->response->json();
        empty($data) ? $exception->response->body() : $data;

    }


    private static function send(array $message) : void
    {
        $text = '<b>' . env('APP_NAME') . '</b>' . PHP_EOL
            . '<b>' . env('APP_ENV') . '</b>' . PHP_EOL
            . '<i>Message:</i>' . PHP_EOL
            . '<code>' . json_encode($message, 64 | 128 | 256) . '</code>';

        $query = [
            'chat_id' => config('telegramLog.chat_id'),
            'text' => $text,
            'parse_mode' => 'html',
        ];
        if (config('sentry.dsn')) {
            $keyboard = ["inline_keyboard" => [[[
                "text" => 'Перейти в sentry',
                "url" => config('app.sentryUrl')
            ]]]];
            $query['reply_markup'] = json_encode($keyboard);
        }

        if (mb_strlen($text) < 4096) {
            Http::get('https://api.telegram.org/bot' . config('telegramLog.token') . '/sendMessage', $query)->throw();
        } else {
            Http::asMultipart()->attach('document', $text, env('APP_NAME') . '.txt')
                ->post('https://api.telegram.org/bot' . config('telegramLog.token') . '/sendDocument', $query)->throw();
        }
    }
}
