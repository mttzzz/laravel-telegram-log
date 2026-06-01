<?php

namespace mttzzz\LaravelTelegramLog;

use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
        } catch (\Throwable $e) {
            /* Telegram — side-channel уведомлений. Его сбой (429 / network /
               connection reset) НЕ должен порождать Sentry-событие или
               пробрасываться наружу — иначе error-storm множит шум
               (consumer уже залогировал исходную ошибку в Sentry). Глотаем. */
        }
    }

    public static function handleObject(object $message) : array | string
    {
        return match (get_class($message)) {
            RequestException::class  => self::handleRequestException($message),
            Collection::class  => $message->toArray(),
            Exception::class  => ['message' => $message->getMessage()],
            default => ['message' => print_r($message, true)]
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
        $text = '*' . env('APP_NAME') . '* ' . PHP_EOL
            . '*' . env('APP_ENV') . '* ' . PHP_EOL
            . '* Message: * ' . PHP_EOL
            . '```json' . json_encode($message, 64 | 128 | 256) . '```';

        $query = [
            'chat_id' => config('telegramLog.chat_id'),
            'text' => $text,
            'parse_mode' => 'Markdown',
        ];
        if (config('sentry.dsn')) {
            $keyboard = ["inline_keyboard" => [[[
                "text" => 'Перейти в sentry',
                "url" => config('app.sentryUrl')
            ]]]];
            $query['reply_markup'] = json_encode($keyboard);
        }

        /* Без ->throw(): 429 / ошибочный статус не должны бросать исключение —
           уведомление просто не доставлено (fire-and-forget). Connection-ошибки
           (cURL) всё равно бросаются HTTP-клиентом и ловятся в log(). */
        if (mb_strlen($text) < 4096) {
            Http::get('https://api.telegram.org/bot' . config('telegramLog.token') . '/sendMessage', $query);
        } else {
            Http::asMultipart()->attach('document', $text, env('APP_NAME') . '.txt')
                ->post('https://api.telegram.org/bot' . config('telegramLog.token') . '/sendDocument', $query);
        }
    }
}
