# <img src=".github/logo.svg?sanitize=true" width="24" height="24" alt="Decompresso"> Decompresso

[![Tests](https://github.com/esplora/decompresso/actions/workflows/phpunit.yml/badge.svg)](https://github.com/esplora/decompresso/actions/workflows/phpunit.yml)

Библиотека для извлечения содержимого архивов.

## Возможности

- **Поддержка паролей**: Работайте с архивами, защищёнными паролем, используя различные способы предоставления паролей.
- **Гибкость обработчиков**: Подключайте и настраивайте обработчики для различных форматов архивов.
- **Интуитивный интерфейс**: Используйте Fluent API для простого конфигурирования и обработки событий успешного или
  неудачного извлечения файлов.

## Установка

Для установки используйте Composer:

```bash
composer require esplora/decompresso
```

## Использование

Вот пример использования библиотеки для извлечения архива:

```php
use Esplora\Decompresso\Extractor;
use Esplora\Decompresso\Handlers\ZipArchiveHandler;
use Esplora\Decompresso\Handlers\GzipArchiveHandler;
use Esplora\Decompresso\Providers\ArrayPasswordProvider;

$extractor = new Extractor();

$extractor
    ->withPasswords(new ArrayPasswordProvider(['123', 'xxx123']))
    ->withHandlers([
        new ZipArchiveHandler(),
        new GzipArchiveHandler(),
    ])
    ->onSuccess(fn() => 'Файлы извлечены успешно')
    ->onFailure(fn() => 'Не удалось распаковать');

// Извлекаем архив и возвращает результат замыкания onSuccess или onFailure
$extractor->extract('/path/to/your/archive.zip', '/path/to/extract/to');
```

Всё строиться с помощью объекта в который вы добавляете обработчики, в примере используется `ZipArchiveHandler` для
ZIP-файлов, но можете создать собственный обработчик для поддержки других форматов или использовать доступные из пакета.

Также вы можете добавить провайдер паролей, в примере используется `ArrayPasswordProvider`, который принимает массив
паролей. Скорее всего вы захотите создать свой провайдер, реализуя `PasswordProviderInterface`,
например, `DataBasePasswordProvider` для получения паролей из базы данных и добавления кеширования.

## TODO

- [ ] Добавить обработчик для RAR-архивов
- [ ] Добавить обработчик для 7z-архивов
- [ ] Добавить проверку целостности в тестах
- [ ] Ввести проверку на расширение файла для обработчика, что бы он пропускал файлы не поддерживаемых форматов

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
