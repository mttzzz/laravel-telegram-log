# Changelog

All notable changes to `LaravelTelegramLog` will be documented in this file.

## Version 2.4.8

### Fixed
- Сбой доставки уведомления больше не амплифицирует ошибки: `Telegram::send()`
  убрал `->throw()`, а `Telegram::log()` не вызывает `Sentry\captureException`
  при сбое (429 / network / connection reset) — провал глотается молча.
  Уведомления — fire-and-forget side-channel; их сбой не должен порождать
  Sentry-события/исключения и плодить error-storm. Также убрана неявная
  зависимость от `sentry/sentry` (которой не было в `require`).

## Version 1.0

### Added
- Everything
