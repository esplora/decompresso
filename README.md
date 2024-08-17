# <img src=".github/logo.svg?sanitize=true" width="24" height="24" alt="Decompresso"> Decompresso

[![Tests](https://github.com/esplora/decompresso/actions/workflows/phpunit.yml/badge.svg)](https://github.com/esplora/decompresso/actions/workflows/phpunit.yml)

это библиотека для извлечения содержимого архивов различных форматов, обеспечивающая гибкость и удобство в работе с архивами, включая те, что защищены паролем.

## Возможности

- **Поддержка архивов с паролем**: Обрабатывайте зашифрованные архивы, используя разные подходы для предоставления паролей.
- **Гибкая система обработчиков**: Легко добавляйте и настраивайте обработчики для различных форматов архивов.
- **Интуитивно понятный интерфейс**: Используйте Fluent API для удобной настройки и обработки событий, связанных с успешным или неудачным извлечением файлов.

## Установка

Установите библиотеку с помощью Composer:

```bash
composer require esplora/decompresso
```


## Использование

Для начала работы создайте экземпляр класса `Extractor` и добавьте необходимые обработчики для форматов архивов.
В следующем примере показано, как использовать `ZipArchiveHandler` для работы с ZIP-файлами, но вы можете добавить
собственные обработчики или использовать встроенные.

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

### Работа с архивами, защищенными паролем

Для работы с архивами, защищенными паролем, добавьте провайдер паролей. 
В следующем примере используется `ArrayPasswordProvider`, который принимает массив паролей.

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

При необходимости, вы можете создать собственный провайдер паролей, реализовав `PasswordProviderInterface`.
Например, для получения паролей из базы данных с кешированием, можно создать
например, `DataBasePasswordProvider`.

### Обработка событий

Для более глубокого контроля над процессом извлечения можно добавлять обработчики событий. 
Это позволит вам получать информацию о причинах неудачного извлечения или реагировать на успешное завершение.

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
- [x] Добавить проверку целостности в тестах
- [x] Ввести проверку на расширение файла для обработчика, что бы он пропускал файлы не поддерживаемых форматов

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
