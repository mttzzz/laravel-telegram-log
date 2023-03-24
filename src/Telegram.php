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
    public static function log($message) : void
    {
        $parsedMessage = match (gettype($message)) {
            "array", => $message,
            "object"=> self::handleObject($message),
            default => ['message' => print_r($message, true)
            ]
        };
        try {
            self::send($parsedMessage);
        }
        catch (Exception $e) {
            captureException($e);
        }
    }

    public static function handleObject(object $message) : array
    {
        return match (get_class($message)) {
            RequestException::class  => self::handleRequestException($message),
            Collection::class  => $message->toArray(),
            Exception::class  => ['message' => $message->getMessage()],
            default => print_r($message, true)
        };
    }

    public static function handleRequestException(RequestException $exception) : array
    {
        $data = $exception->response->json();
        if (empty($data)) {
            xml_parse_into_struct(xml_parser_create(), $exception->response->body(), $data, $index);
        }
        return empty($data) ?  ['message' => (string)$exception->response->body()] :$data;
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
