<?php

declare(strict_types=1);

namespace Esplora\Decompresso\Tests;

use Esplora\Decompresso\Handlers\ZipArchiveHandler;
use PHPUnit\Framework\TestCase;

/**
 * Тесты для обработчика ZIP-архивов.
 */
class ZipArchiveHandlerTest extends TestCase
{
    /**
     * Возвращает полный путь к директории фиктивных файлов.
     *
     * @return string
     */
    protected function getFixturesDir(): string
    {
        return __DIR__ . '/../fixtures/zip/';
    }

    /**
     * Возвращает полный путь к директории извлечения.
     *
     * @return string
     */
    protected function getExtractionPath(): string
    {
        return __DIR__ . '/../extracted/';
    }

    /**
     * Удаляет каталог и все его содержимое рекурсивно.
     *
     * @param string $dir Каталог для удаления.
     */
    private function deleteDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->deleteDir($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->deleteDir($this->getExtractionPath());
    }

    protected function tearDown(): void
    {
        $this->deleteDir($this->getExtractionPath());
        parent::tearDown();
    }


    /**
     * Возвращает путь к архиву для тестов.
     *
     * @return string
     */
    protected function getArchivePath(): string
    {
        return $this->getFixturesDir() . 'simple.zip';
    }

    /**
     * Возвращает список паролей для тестов.
     *
     * @return array
     */
    protected function getPasswords(): array
    {
        return ['12345']; // Список паролей для теста защищенного архива
    }

    /**
     * Возвращает ожидаемое имя файла после извлечения.
     *
     * @return string
     */
    protected function getExpectedFileName(): string
    {
        return 'simple.txt'; // Ожидаемое имя файла после извлечения
    }

    public function testExtractionSuccess()
    {
        $handler = new ZipArchiveHandler();

        $result = $handler->extract($this->getArchivePath(), $this->getExtractionPath());


        $this->assertTrue($result);
        $this->assertFileExists($this->getExtractionPath() . $this->getExpectedFileName());
    }

    public function testExtractionSuccessWithPassword()
    {
        $archivePath = $this->getFixturesDir() . 'protected.zip';

        $handler = new ZipArchiveHandler();

        $result = $handler->extract($archivePath, $this->getExtractionPath(), $this->getPasswords());

        $this->assertTrue($result);
        $this->assertFileExists($this->getExtractionPath() . $this->getExpectedFileName());
    }

    public function testExtractionFailureOnPassword()
    {
        $archivePath = $this->getFixturesDir() . 'protected.zip';

        $handler = new ZipArchiveHandler();

        $result = $handler->extract($archivePath, $this->getExtractionPath(), ['wrongpassword']);

        $this->assertFalse($result);
        $this->assertFileDoesNotExist($this->getExtractionPath() . $this->getExpectedFileName());
    }
}
