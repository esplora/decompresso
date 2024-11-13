# <img src=".github/logo.svg?sanitize=true" width="32" height="32" alt="Lumos"> Lumos

[![Tests](https://github.com/esplora/decompresso/actions/workflows/phpunit.yml/badge.svg)](https://github.com/esplora/decompresso/actions/workflows/phpunit.yml)
[![Psalm](https://github.com/esplora/decompresso/actions/workflows/psalm.yml/badge.svg)](https://github.com/esplora/decompresso/actions/workflows/psalm.yml)

Lumos is a universal library designed to provide a unified interface for processing various file types. Whether you need to unlock passwords from office documents or extract contents from compressed archives, Lumos simplifies these tasks with ease and efficiency.

## Features

- **Unlock Password-Protected Files**: Remove passwords from encrypted office documents (PDF, DOC, etc.) effortlessly.
- **Extract Archives**: Unpack various archive formats (ZIP, RAR, etc.), including those secured with passwords.
- **Flexible Handler System**: Easily add and configure handlers for different file formats and operations.
- **Intuitive Interface**: Utilize a fluent API for convenient configuration and handling of successful or failed operations.


## External Dependencies

Lumos relies on the following third-party tools for specific operations:

| **File Type**         | **Tool**                     | **Description**                              |
|-----------------------|------------------------------|----------------------------------------------|
| PDF                   | [qpdf](https://github.com/qpdf/qpdf) | Handles processing and unlocking of PDF files. |
| Office Formats        | [msoffcrypto-tool](https://github.com/msoffice/msoffcrypto-tool) | Manages decryption of Microsoft Office files. |
| Zip Archive           | Built-in PHP classes         | Uses PHP's ZipArchive. |


## Installation

Install the library using Composer:

```bash
composer require esplora/lumos
```

## Usage

To get started, create an instance of the `FileProcessor` class and add the necessary handlers for file formats. The example below demonstrates using `ZipArchiveAdapter` for ZIP files, but you can add your own handlers or use built-in ones.

```php
use Esplora\Lumos\FileProcessor;
use Esplora\Lumos\Adapters\ZipAdapter;
use Esplora\Lumos\Adapters\GzipAdapter;

// Create a new FileProcessor instance to manage file processing
$fileProcessor = new FileProcessor();

// Specify which file handlers will be used
$fileProcessor->withAdapters([
    new ZipAdapter(),
    new GzipAdapter(),
]);

// Process a file (returns a boolean depending on the outcome)
$fileProcessor->process('/path/to/your/archive.zip', '/path/to/extract/to');
```

### Handling Password-Protected Files

To work with password-protected documents, add a password provider. The example below uses `ArrayPasswordProvider`, which accepts an array of passwords.

```php
use Esplora\Lumos\FileProcessor;
use Esplora\Lumos\Adapters\ZipAdapter;
use Esplora\Lumos\Providers\ArrayPasswordProvider;

$fileProcessor = new FileProcessor();

$fileProcessor
    ->withPasswords(new ArrayPasswordProvider([
        'qwerty',
        'xxx123',
    ]))
    ->withAdapters([
        new ZipAdapter(),
        // Add more adapters as needed
    ]);

// Process the file and returns a boolean depending on the outcome
$fileProcessor->process('/path/to/your/document.docx', '/path/to/save/to');
```

If needed, you can create your own password provider by implementing the `PasswordProviderInterface`.

### Event Handling

For more control over the file processing, you can add event handlers. This allows you to receive information about the reasons for failures or respond to successful completions.

```php
use Esplora\Lumos\FileProcessor;
use Esplora\Lumos\Handlers\ZipArchiveHandler;
use Esplora\Lumos\Providers\ArrayPasswordProvider;

$fileProcessor = new FileProcessor();

$fileProcessor
    ->withPasswords(new ArrayPasswordProvider([
        'qwerty',
        'xxx123',
    ]))
    ->withAdapters([
        new ZipArchiveAdapter(),
        // Add more adapters as needed
    ])
    
    // Define logic to execute on successful processing
    ->onSuccess(fn() => true)
    
    // Handle cases where processing fails due to an incorrect password
    ->onPasswordFailure(fn() => false)
    
    // Handle any other errors encountered during processing
    ->onFailure(fn() => false)

// Processes the file and returns the result of the closure
$fileProcessor->process('/path/to/your/archive.zip', '/path/to/extract/to');
```

## TODO

- [x] Добавить файл ответа, который бы разделял "не смогли обработать" из-за ошибки и "не смогли обработать так как не подошёл пароль".
- [ ] Добавить обработчик для RAR-архивов.
- [ ] Добавить обработчик для 7z-архивов.
- [x] Добавить проверку целостности в тестах.
- [x] Ввести проверку на расширение файла для обработчика, чтобы он пропускал файлы неподдерживаемых форматов.
- [ ] Подумать над тем, чтобы передавать сразу MIME-type в обработчик, а не создавать его каждый раз. - Нет. Будет нарушение ответственности.
- [x] Не обращаться к провайдеру паролей, если он не нужен.
- [ ] Нужно обновить комментарии! Про пароль тоже!

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
