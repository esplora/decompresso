# <img src=".github/logo.svg?sanitize=true" width="32" height="32" alt="Lumos"> Lumos

[![Tests](https://github.com/esplora/decompresso/actions/workflows/phpunit.yml/badge.svg)](https://github.com/esplora/decompresso/actions/workflows/phpunit.yml)
[![Quality Assurance](https://github.com/esplora/lumos/actions/workflows/quality.yml/badge.svg)](https://github.com/esplora/lumos/actions/workflows/quality.yml)
[![Coding Guidelines](https://github.com/esplora/lumos/actions/workflows/php-cs-fixer.yml/badge.svg)](https://github.com/esplora/lumos/actions/workflows/php-cs-fixer.yml)

Lumos is a universal library designed to provide a unified interface for processing various file types. Whether you need to unlock passwords from office documents or extract contents from compressed archives, Lumos simplifies these tasks with ease and efficiency.

## Features

- **Unlock Password-Protected Files**: Remove passwords from encrypted office documents (PDF, DOC, etc.) effortlessly.
- **Extract Archives**: Unpack various archive formats (ZIP, RAR, etc.), including those secured with passwords.
- **Flexible Handler System**: Easily add and configure handlers for different file formats and operations.
- **Intuitive Interface**: Utilize a fluent API for convenient configuration and handling of successful or failed operations.


## External Dependencies

Lumos relies on the following third-party tools for specific operations:

| **File Type**        | **Tool**                                                         | **Purpose**                                               |
|----------------------|------------------------------------------------------------------|-----------------------------------------------------------|
| PDF                  | [qpdf](https://github.com/qpdf/qpdf)                             | Unlocks and processes encrypted or protected PDF files.   |
| Microsoft Office     | [msoffcrypto-tool](https://github.com/msoffice/msoffcrypto-tool) | Decrypts password-protected Microsoft Office documents.   |
| Archive (ZIP, 7z)    | [7-zip](https://www.7-zip.org/)                                  | Extracts and manages compressed archives (ZIP, 7z, etc.). |

## Installation

Install the library using Composer:

```bash
composer require esplora/lumos
```

## Usage

To get started, create an instance of the `Extractor` class and add the necessary handlers for file formats. The example below demonstrates using `ZipArchiveAdapter` for ZIP files, but you can add your own handlers or use built-in ones.

```php
use Esplora\Lumos\Extractor;
use Esplora\Lumos\Adapters\SevenZipAdapter;

$extractor = new Extractor();

// Specify which file handlers will be used
$extractor->withAdapters([
    new SevenZipAdapter(),
]);

// Process a file (returns a boolean depending on the outcome)
$extractor->process('/path/to/your/archive.zip', '/path/to/extract/to');
```

### Handling Password-Protected Files

To work with password-protected documents, add a password provider. The example below uses `ArrayPasswordProvider`, which accepts an array of passwords.

```php
use Esplora\Lumos\Extractor;
use Esplora\Lumos\Adapters\SevenZipAdapter;
use Esplora\Lumos\Providers\ArrayPasswordProvider;

$extractor = new Extractor();

$extractor
    ->withPasswords(new ArrayPasswordProvider([
        'qwerty',
        'xxx123',
    ]))
    ->withAdapters([
        new SevenZipAdapter(),
        // Add more adapters as needed
    ]);

// Process the file and returns a boolean depending on the outcome
$extractor->process('/path/to/your/archive.zip', '/path/to/save/to');
```

If needed, you can create your own password provider by implementing the `PasswordProviderInterface`.

### Event Handling

For more control over the file processing, you can add event handlers. This allows you to receive information about the reasons for failures or respond to successful completions.

```php
use Esplora\Lumos\Extractor;
use Esplora\Lumos\Adapters\SevenZipAdapter;
use Esplora\Lumos\Providers\ArrayPasswordProvider;

$extractor = new Extractor();

$extractor
    ->withPasswords(new ArrayPasswordProvider([
        'qwerty',
        'xxx123',
    ]))
    ->withAdapters([
        new SevenZipAdapter(),
        // Add more adapters as needed
    ])
    
    // Define logic to execute on successful processing
    ->onSuccess(fn() => true)
    
    // Handle cases where processing fails due to an incorrect password
    ->onPasswordFailure(fn() => false)
    
    // Handle any other errors encountered during processing
    ->onFailure(fn() => false)

// Processes the file and returns the result of the closure
$extractor->process('/path/to/your/archive.zip', '/path/to/extract/to');
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
