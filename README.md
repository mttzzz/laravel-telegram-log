# Laravel Telegram Log

- Send log to telegram chat.
- Accept string, array, object, json;

### Install
    composer require mttzzz/laravel-telegram-log

### Publish config
    php artisan vendor:publish --provider mttzzz\laravelTelegramLog\TelegramLogServiceProvider
    
Edit app/config/telegramLog.php and fill your Telegram Bot token and chatId or add env variables.
```php
<?php

return [
// Telegram Bot Token
    'token' => env('TELEGRAM_LOG_BOT_TOKEN', '111111:AAF99VnmhsE6HQtH6vsQaBRLctxXs4-UpdY'),

// Telegram Chat Id
    'chat_id' => env('TELEGRAM_LOG_CHAT_ID', '-1111111111111'),

];
```

### Usage
```php
Telegram::log('test');
Telegram::log(['test' => 'test']);
Telegram::log({"test" : "test"});
```
