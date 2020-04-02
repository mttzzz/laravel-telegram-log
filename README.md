# Laravel Telegram Log

- Send log to telegram chat.
- Accept string, array, object, json;

### Install
    composer require mttzzz/laravel-telegram-log

### Publish config
    php artisan vendor:publish --provider mttzzz\laravelTelegramLog\TelegramLogServiceProvider
    
Edit app/config/telegramLog.php and fill your Telegram Bot token and chatId or add env variables.
```php

return [
     // Telegram Bot Token
    'token' => env('TELEGRAM_LOG_BOT_TOKEN', '111111:AAF99VnmhsE6HQtH6vsQaBRLctxXs4-UpdY'),

    // Telegram Chat Id
    'chat_id' => env('TELEGRAM_LOG_CHAT_ID', '-1111111111111'),
];

```

Edit app/config/logging.php and add channel "telegram" and change channel "stack" value 'channels' by then (example). 

```php
return [
    'stack' => [
         'driver' => 'stack',
         'channels' => ['daily', 'telegram'],
         'ignore_exceptions' => false,
    ],
    'telegram' => [
         'driver' => 'monolog',
         'handler' => \mttzzz\laravelTelegramLog\HandlerTelegramLogger::class
    ]
];
```

### Usage
```php
Telegram::log('test');
Telegram::log(['test' => 'test']);
Telegram::log({"test" : "test"});
```

