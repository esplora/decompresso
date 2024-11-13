# <img src=".github/logo.svg?sanitize=true" width="32" height="32" alt="Lumos"> Lumos

[![Tests](https://github.com/esplora/decompresso/actions/workflows/phpunit.yml/badge.svg)](https://github.com/esplora/decompresso/actions/workflows/phpunit.yml)
[![Quality Assurance](https://github.com/esplora/lumos/actions/workflows/quality.yml/badge.svg)](https://github.com/esplora/lumos/actions/workflows/quality.yml)
[![Coding Guidelines](https://github.com/esplora/lumos/actions/workflows/php-cs-fixer.yml/badge.svg)](https://github.com/esplora/lumos/actions/workflows/php-cs-fixer.yml)

Lumos is a universal library designed to provide a unified interface for processing various file types. Whether you need
to unlock passwords from office documents or extract contents from compressed archives, Lumos simplifies these tasks
with ease and efficiency.

## Features

- **Unlock Password-Protected Files**: Remove passwords from encrypted office documents (PDF, DOC, etc.) effortlessly.
- **Extract Archives**: Unpack various archive formats (ZIP, RAR, etc.), including those secured with passwords.
- **Flexible Handler System**: Easily add and configure handlers for different file formats and operations.
- **Intuitive Interface**: Utilize a fluent API for convenient configuration and handling of successful or failed
  operations.

## External Dependencies

Lumos relies on the following third-party tools for specific operations:

| **File Type**     | **Tool**                                                         | **Purpose**                                               |
|-------------------|------------------------------------------------------------------|-----------------------------------------------------------|
| PDF               | [qpdf](https://github.com/qpdf/qpdf)                             | Unlocks and processes encrypted or protected PDF files.   |
| Microsoft Office  | [msoffcrypto-tool](https://github.com/msoffice/msoffcrypto-tool) | Decrypts password-protected Microsoft Office documents.   |
| Archive (ZIP, 7z) | [7-zip](https://www.7-zip.org/)                                  | Extracts and manages compressed archives (ZIP, 7z, etc.). |

## Installation

Install the library using Composer:

```bash
composer require esplora/lumos
```

## Usage

To get started, create an instance of the `Extractor` class and add the necessary handlers for file formats. The example
below demonstrates using `ZipArchiveAdapter` for ZIP files, but you can add your own handlers or use built-in ones.

```php
use Esplora\Lumos\Extractor;
use Esplora\Lumos\Adapters\SevenZipAdapter;

Extractor::make([
    // Specify which file handlers will be used
    new SevenZipAdapter(),
])
// Process a file (returns a boolean depending on the outcome)
->process('/path/to/your/archive.zip', '/path/to/extract/to');
```

### Handling Password-Protected Files

To work with password-protected documents, add a password provider. The example below uses `ArrayPasswordProvider`,
which accepts an array of passwords.

```php
use Esplora\Lumos\Extractor;
use Esplora\Lumos\Adapters\SevenZipAdapter;
use Esplora\Lumos\Providers\ArrayPasswordProvider;

Extractor::make([
    new SevenZipAdapter(),
])
    ->withPasswords(new ArrayPasswordProvider([
        'qwerty',
        'xxx123',
    ]))
    ->process('/path/to/your/archive.zip', '/path/to/save/to');
```

If needed, you can create your own password provider by implementing the `PasswordProviderInterface`.

### Event Handling

For more control over the file processing, you can add event handlers. This allows you to receive information about the
reasons for failures or respond to successful completions.

```php
use Esplora\Lumos\Extractor;
use Esplora\Lumos\Adapters\SevenZipAdapter;
use Esplora\Lumos\Providers\ArrayPasswordProvider;

Extractor::make([
    new SevenZipAdapter(),
])
    ->withPasswords(new ArrayPasswordProvider([
        'qwerty',
        'xxx123',
    ]))
    ->onSuccess(function ($file, $output) {
        // Processing completed successfully!
    })
    ->onPasswordFailure(function ($file, $output) {
        // The password was incorrect
    })
    ->onFailure(function ($throwable, $file, $output) {
        // Handle the failure
    })
    ->process('/path/to/your/archive.zip', '/path/to/save/to');
```

### Local Testing

We understand that you may need to test your library locally, for example, for debugging purposes or if you want to
contribute to the project. To remain environment-independent, we recommend passing the path to the executable files of
your dependencies via the constructor:

```php
use Esplora\Lumos\Adapters\SevenZipAdapter;

new SevenZipAdapter('/usr/bin/7z'),
```

For convenience, we also support using environment variables from a `.env` file to store paths to dependency executables
in one place. To do this, create a `.env` file at the root of your project and add the environment variables as shown in
the `.env.example`.

> [!WARNING]  
> Environment variables from the `.env` file will be loaded only for local testing and are added solely for the
> convenience of developing this package.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
