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

Всё строиться с помощью объекта в который вы добавляете обработчики, в примере используется `ZipArchiveHandler` для
ZIP-файлов, но можете создать собственный обработчик для поддержки других форматов или использовать доступные из пакета.

Вот пример использования библиотеки для извлечения архива:

```php
use Esplora\Decompresso\Extractor;
use Esplora\Decompresso\Handlers\ZipArchiveHandler;
use Esplora\Decompresso\Handlers\GzipArchiveHandler;

$extractor = new Extractor();

$extractor->withHandlers([
  new ZipArchiveHandler(),
  new GzipArchiveHandler(),
])

// Извлекаем архив и возвращает результат true/false
$extractor->extract('/path/to/your/archive.zip', '/path/to/extract/to');
```

Для работы с архивами, защищенными паролем, добавьте провайдер паролей. 
В примере используется `ArrayPasswordProvider`, который принимает массив
паролей. 

```php
use Esplora\Decompresso\Extractor;
use Esplora\Decompresso\Handlers\ZipArchiveHandler;
use Esplora\Decompresso\Handlers\GzipArchiveHandler;
use Esplora\Decompresso\Providers\ArrayPasswordProvider;

$extractor = new Extractor();

$extractor
    ->withPasswords(new ArrayPasswordProvider([
        'qwerty',
        'xxx123',
    ]))
    ->withHandlers([
        new ZipArchiveHandler(),
        new GzipArchiveHandler(),
    ])

// Извлекаем архив и возвращает результат true/false
$extractor->extract('/path/to/your/archive.zip', '/path/to/extract/to');
```

Скорее всего вы захотите создать свой провайдер, реализуя `PasswordProviderInterface`,
например, `DataBasePasswordProvider` для получения паролей из базы данных и добавления кеширования.


Когда вы захотите понять больше, о том почему извлечение не удалось, вы можете добавить обработчик событий:

```php 
use Esplora\Decompresso\Extractor;
use Esplora\Decompresso\Handlers\ZipArchiveHandler;
use Esplora\Decompresso\Handlers\GzipArchiveHandler;
use Esplora\Decompresso\Providers\ArrayPasswordProvider;

$extractor = new Extractor();

$extractor
    ->withPasswords(new ArrayPasswordProvider([
        'qwerty',
        'xxx123',
    ]))
    ->withHandlers([
        new ZipArchiveHandler(),
        new GzipArchiveHandler(),
    ])
    ->onSuccess(fn() => true) // 'Файлы извлечены успешно'
    ->onPasswordFailure(fn() => false) // 'Не удалось распаковать так как не подошёл пароль'
    ->onFailure(fn() => false) // 'Не удалось распаковать из-за внутренней ошибки';

// Извлекаем архив и возвращает результат замыкания
$extractor->extract('/path/to/your/archive.zip', '/path/to/extract/to');
```


## TODO
- [x] Добавить файл ответа, который бы разделял "не смогли распоковать" из-за ошибки и "не смогли распоковать так как не подошёл пароль"
- [ ] Добавить обработчик для RAR-архивов
- [ ] Добавить обработчик для 7z-архивов
- [ ] Добавить проверку целостности в тестах
- [x] Ввести проверку на расширение файла для обработчика, что бы он пропускал файлы не поддерживаемых форматов

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
