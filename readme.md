# Decompresso

Это PHP-библиотека для извлечения архивов с поддержкой паролей.

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
use Esplora\Decompresso\Providers\ArrayPasswordProvider;

// Создаем провайдер паролей
$passwordProvider = new ArrayPasswordProvider(['123', 'xxx123']);

// Создаем обработчик архива
$archiveHandler = new ZipArchiveHandler();

// Создаем объект Extractor
$extractor = new Extractor();

// Настраиваем Extractor
$extractor->withPasswords($passwordProvider)
          ->withHandler($archiveHandler)
          ->onSuccess(fn($filePath) => $filePath . ' извлечен успешно.')
          ->onFailure(fn($filePath) => 'Не удалось извлечь ' . $filePath);

// Извлекаем архив
$extractor->extract('/path/to/your/archive.zip', '/path/to/extract/to');
```