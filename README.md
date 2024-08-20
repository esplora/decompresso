# <img src=".github/logo.svg?sanitize=true" width="32" height="32" alt="Decompresso"> Decompresso

[![Tests](https://github.com/esplora/decompresso/actions/workflows/phpunit.yml/badge.svg)](https://github.com/esplora/decompresso/actions/workflows/phpunit.yml)
[![Psalm](https://github.com/esplora/decompresso/actions/workflows/psalm.yml/badge.svg)](https://github.com/esplora/decompresso/actions/workflows/psalm.yml)

Decompresso is a library designed for extracting contents from various archive formats, providing flexibility and ease
of use, including support for password-protected archives.

## Features

- **Password-Protected Archives**: Handle encrypted archives with various methods for supplying passwords.
- **Flexible Handler System**: Easily add and configure handlers for different archive formats.
- **Intuitive Interface**: Utilize a fluent API for convenient configuration and handling of successful or failed
  extraction events.

## Installation

Install the library using Composer:

```bash
composer require esplora/decompresso
```

## Usage

To get started, create an instance of the `Extractor` class and add the necessary handlers for archive formats. The
example below demonstrates using `ZipArchiveAdapter` for ZIP files, but you can add your own handlers or use built-in
ones.

```php
use Esplora\Decompresso\Extractor;
use Esplora\Decompresso\Adapters\ZipArchiveAdapter;
use Esplora\Decompresso\Adapters\GzipArchiveAdapter;

// Create a new Extractor instance to manage the extraction process
$extractor = new Extractor();

// Specify which archive handlers will be used
$extractor->withAdapters([
    new ZipArchiveAdapter(),
    new GzipArchiveAdapter(),
]);

// Returns a boolean depending on the outcome of the extraction process
$extractor->extract('/path/to/your/archive.zip', '/path/to/extract/to');
```

### Handling Password-Protected Archives

To work with password-protected archives, add a password provider. The example below uses `ArrayPasswordProvider`, which
accepts an array of passwords.

```php
use Esplora\Decompresso\Extractor;
use Esplora\Decompresso\Adapters\ZipArchiveAdapter;
use Esplora\Decompresso\Adapters\GzipArchiveAdapter;
use Esplora\Decompresso\Providers\ArrayPasswordProvider;

$extractor = new Extractor();

$extractor
    ->withPasswords(new ArrayPasswordProvider([
        'qwerty',
        'xxx123',
    ]))
    ->withAdapters([
        new ZipArchiveAdapter(),
        new GzipArchiveAdapter(),
    ]);

// Returns a boolean depending on the outcome of the extraction process
$extractor->extract('/path/to/your/archive.zip', '/path/to/extract/to');
```

If needed, you can create your own password provider by implementing the `PasswordProviderInterface`. For example,
a `DataBasePasswordProvider` could be created for fetching passwords from a database with caching.

If you don’t have a password database but want to try all possible combinations, you can
use [SecLists](https://github.com/danielmiessler/SecLists/tree/master/Passwords) as a source of popular passwords for
brute-forcing.

### Event Handling

For more control over the extraction process, you can add event handlers. This allows you to receive information about
the reasons for extraction failures or respond to successful completions.

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
    ->withAdapters([
        new ZipArchiveAdapter(),
        new GzipArchiveAdapter(),
    ])
    
    // Define logic to execute on successful extraction
    ->onSuccess(fn() => true)
    
    // Handle cases where extraction fails due to an incorrect password
    ->onPasswordFailure(fn() => false)
    
    // Handle any other errors encountered during extraction
    ->onFailure(fn() => false)

// Extracts the archive and returns the result of the closure
$extractor->extract('/path/to/your/archive.zip', '/path/to/extract/to');
```

## TODO

- [x] Добавить файл ответа, который бы разделял "не смогли распоковать" из-за ошибки и "не смогли распоковать так как не
  подошёл пароль"
- [ ] Добавить обработчик для RAR-архивов
- [ ] Добавить обработчик для 7z-архивов
- [x] Добавить проверку целостности в тестах
- [x] Ввести проверку на расширение файла для обработчика, что бы он пропускал файлы не поддерживаемых форматов
- [ ] Подумать над тем, чо бы передавать сразу MIME-type в обработчик, а не создавать его каждый раз. - Нет. Будет
  нарушение ответственности.
- [x] Не обращаться к провайдеру паролей, если он не нужен.
- [ ] Нужно обновить комментарии! Про пароль тоже!

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
