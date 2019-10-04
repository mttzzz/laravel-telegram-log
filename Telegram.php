<?php

namespace mttzzz\laravelTelegramLog;

use GuzzleHttp\Client;

class Telegram
{
    public static function log($note)
    {
        if (is_array($note) || is_object($note)) {
            $note = json_encode($note, 64 | 128 | 256);
        } else {
            $noteArray = json_decode($note);
            if (json_last_error() == JSON_ERROR_NONE) {
                $note = json_encode($noteArray, 64 | 128 | 256);
            }
        }

        $token = '465824247:AAF99VnmhsE6HQtH6vsQaBRLctxXs4-UpdY';
        $chat_id = '-1001353020906';

        $message = '<b>' . env('APP_NAME') . '</b>' . PHP_EOL
            . '<b>' . env('APP_ENV') . '</b>' . PHP_EOL
            . '<i>Message:</i>' . PHP_EOL
            . '<code>' . $note . '</code>';

        try {
            $ids = explode(',', $chat_id);
            $client = new Client();

            foreach ($ids as $id) {
                $query = ['text' => $message, 'chat_id' => $id, 'parse_mode' => 'html'];
                $client->get('https://api.telegram.org/bot' . $token . '/sendMessage', ['query' => $query]);
            }
        } catch (\Exception $e) {

        }
    }
}
