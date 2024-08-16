# <img src=".github/logo.svg?sanitize=true" width="24" height="24" alt="Decompresso"> Decompresso

Библиотека для извлечения содержимого архивов.

## Возможности

- **Поддержка паролей**: Работайте с архивами, защищёнными паролем, используя различные способы предоставления паролей.
- **Гибкость обработчиков**: Подключайте и настраивайте обработчики для различных форматов архивов.
- **Интуитивный интерфейс**: Используйте Fluent API для простого конфигурирования и обработки событий успешного или неудачного извлечения файлов.

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

$extractor = new Extractor();

$extractor
          ->withPasswords(new ArrayPasswordProvider(['123', 'xxx123']))
          ->withHandler(new ZipArchiveHandler())
          ->onSuccess(fn() => 'Файлы извлечены успешно')
          ->onFailure(fn() => 'Не удалось распоковать');

// Извлекаем архив
$extractor->extract('/path/to/your/archive.zip', '/path/to/extract/to');
```


### Как это работает

#### Провайдеры паролей

Библиотека поддерживает различные провайдеры паролей для работы с защищёнными архивами. 
В примере используется `ArrayPasswordProvider`, который принимает массив паролей.
Вы можете создать свой провайдер, реализуя `PasswordProviderInterface`, например,
`DataBasePasswordProvider` для получения паролей из базы данных и добавления кеширования.

#### Обработчики архивов

Для работы с архивами библиотека использует обработчики. 
В примере используется `ZipArchiveHandler` для ZIP-файлов. 
Вы можете создать собственный обработчик для поддержки других форматов архивов.
