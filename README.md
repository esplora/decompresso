# <img src=".github/logo.svg?sanitize=true" width="32" height="32" alt="Lumos"> Lumos

[![Tests](https://github.com/esplora/decompresso/actions/workflows/phpunit.yml/badge.svg)](https://github.com/esplora/decompresso/actions/workflows/phpunit.yml)
[![Quality Assurance](https://github.com/esplora/lumos/actions/workflows/quality.yml/badge.svg)](https://github.com/esplora/lumos/actions/workflows/quality.yml)
[![Coding Guidelines](https://github.com/esplora/lumos/actions/workflows/php-cs-fixer.yml/badge.svg)](https://github.com/esplora/lumos/actions/workflows/php-cs-fixer.yml)

Lumos is a library that provides a interface for removing passwords from protected documents and extracting
content from archives, making these tasks simple and accessible.

## External Dependencies

Lumos relies on the following third-party tools for specific operations.
Each adapter is provided out of the box in the `Esplora\Lumos\Adapters\*` namespace:

| **File Type**     | **Tool**                                                         | **Purpose**                                             | **Adapter Class**         |
|-------------------|------------------------------------------------------------------|---------------------------------------------------------|---------------------------|
| PDF               | [qpdf](https://github.com/qpdf/qpdf)                             | Unlocks and processes encrypted or protected PDF files. | QpdfAdapter               |
| Microsoft Office  | [msoffcrypto-tool](https://github.com/msoffice/msoffcrypto-tool) | Decrypts password-protected Microsoft Office documents. | MSOfficeCryptoToolAdapter |
| Archive (ZIP, 7z) | [7-zip](https://www.7-zip.org/)                                  | Extracts and decrypts compressed archives               | SevenZipAdapter           |

## Installation

Install the library using Composer:

```bash
composer require esplora/lumos
```

## Usage

To get started, create an instance of the `Extractor` class and add the necessary adapters for file formats. The example
below demonstrates using `SevenZipAdapter` for archive, but you can add your own adapters or use built-in ones.

```php
use Esplora\Lumos\Extractor;
use Esplora\Lumos\Adapters\SevenZipAdapter;

Extractor::make([
    // Specify which file adapters will be used
    new SevenZipAdapter(),
])
->extract('/path/to/your/archive.zip', '/path/to/extract/to');
// Process a file (returns a boolean depending on the outcome)
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
    ->extract('/path/to/your/archive.zip', '/path/to/save/to');
```

If needed, you can create your own password provider by implementing the `PasswordProviderInterface`.

> [!TIP]
> If you don’t have a password database but want to try all possible combinations, you can
> use [SecLists](https://github.com/danielmiessler/SecLists/tree/master/Passwords) as a source of popular passwords for
> brute-forcing.

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
    ->extract('/path/to/your/archive.zip', '/path/to/save/to');
```

### Testing

Testing an application that depends on other services can be challenging, but this should not prevent you from
contributing to the project.

For adapters that depend on executable files, you can pass the path via the constructor:

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
