<?php

namespace mttzzz\laravelTelegramLog;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class Telegram
{
    public static function log($note)
    {
        if ($note instanceof Exception) {
            $note = [
                'message' => $note->getMessage(),
                'line' => $note->getLine(),
                'file' => $note->getFile()
            ];
            $note = json_encode($note, 64 | 128 | 256 | 2048);
            if (config('sentry.dsn')) {
                $sentryId = mb_split('/', config('sentry.dsn'))[3];
                $url = 'https://sentry.io/organizations/pushka/issues/?project=' . $sentryId;
                $keyboard = ["inline_keyboard" => [[[
                    "text" => 'Перейти в sentry',
                    "url" => $url
                ]]]];
            }
        } elseif (is_array($note) || is_object($note)) {
            if (isset($note['message'])) {
                $message = str_replace(['\n', '   ', "\n"], '', $note['message']);
                $note = compact('message');
            }
            $note = json_encode($note, 64 | 128 | 256 | 2048);
        } else {

            $noteArray = json_decode(urldecode($note),1);
            if (json_last_error() == JSON_ERROR_NONE) {
                $note = json_encode($noteArray, 64 | 128 | 256 | 2048 );
            }
        }
        $token = config('telegramLog.token');
        $chat_id = config('telegramLog.chat_id');
        $message = '<b>' . env('APP_NAME') . '</b>' . PHP_EOL
            . '<b>' . env('APP_ENV') . '</b>' . PHP_EOL
            . '<i>Message:</i>' . PHP_EOL
            . '<code>' . $note . '</code>';

        try {
            $ids = explode(',', $chat_id);
            $client = new Client();


            foreach ($ids as $id) {
                $query = ['text' => $message, 'chat_id' => $id, 'parse_mode' => 'html'];
                if (isset($keyboard)) {
                    $query['reply_markup'] = json_encode($keyboard);
                }
                $client->get('https://api.telegram.org/bot' . $token . '/sendMessage', ['query' => $query]);
            }
        } catch (Exception $e) {
            Telegram::log('Telegram Log DIE!!! Exception');
            Telegram::log($e->getMessage());

        }
    }
}
