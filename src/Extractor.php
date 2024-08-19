<?php

namespace Esplora\Decompresso;

use Esplora\Decompresso\Contracts\ArchiveAdapterInterface;
use Esplora\Decompresso\Contracts\PasswordProviderInterface;
use Esplora\Decompresso\Providers\ArrayPasswordProvider;
use RuntimeException;

/**
 * Class for extracting archives with support for passwords and archive handlers.
 *
 * This class manages the extraction process, supporting various archive types through handlers and
 * using different passwords via a password provider.
 */
class Extractor
{
    /**
     * Password provider for handling protected archives.
     *
     * @var PasswordProviderInterface
     */
    protected PasswordProviderInterface $passwordProvider;

    /**
     * Array of archive handlers for extracting archives.
     *
     * @var ArchiveAdapterInterface[]
     */
    protected array $adapters = [];

    /**
     * Callback for handling failed extractions.
     *
     * @var callable
     */
    protected $failureCallback;

    /**
     * Callback for handling successful extractions.
     *
     * @var callable
     */
    protected $successCallback;

    /**
     * Callback for handling failed password attempts.
     *
     * @var callable
     */
    protected $passwordFailureCallback;

    /**
     * Constructor for the Extractor class.
     *
     * Initializes default handlers for successful and failed extractions.
     */
    public function __construct()
    {
        // Default password provider with an empty password list.
        $this->passwordProvider = new ArrayPasswordProvider([]);

        // Default callback for failed extraction.
        $this->failureCallback = fn (\Throwable $exception) => false;

        // Default callback for successful extraction.
        $this->successCallback = fn () => true;

        // Default callback for password failure.
        $this->passwordFailureCallback = fn () => false;
    }

    /**
     * Sets the password provider for handling protected archives.
     *
     * @param PasswordProviderInterface $provider The password provider to use.
     *
     * @return $this For method chaining.
     */
    public function withPasswords(PasswordProviderInterface $provider): self
    {
        $this->passwordProvider = $provider;

        return $this;
    }

    /**
     * Adds an archive handler to support different archive formats.
     *
     * @param ArchiveAdapterInterface $handler The archive handler.
     *
     * @return $this For method chaining.
     */
    public function withAdapter(ArchiveAdapterInterface $handler): self
    {
        $this->adapters[] = $handler;

        return $this;
    }

    /**
     * Adds multiple archive handlers.
     *
     * @param ArchiveAdapterInterface[] $handlers Array of archive handlers.
     *
     * @return $this For method chaining.
     */
    public function withAdapters(array $handlers): self
    {
        foreach ($handlers as $handler) {
            $this->withAdapter($handler);
        }

        return $this;
    }

    /**
     * Sets the callback for handling failed extractions.
     *
     * @param callable $callback Callback for handling extraction failures.
     *
     * @return $this For method chaining.
     */
    public function onFailure(callable $callback): self
    {
        $this->failureCallback = $callback;

        return $this;
    }

    /**
     * Sets the callback for handling password failures.
     *
     * @param callable $callback Callback for handling password failures.
     *
     * @return $this
     */
    public function onPasswordFailure(callable $callback): self
    {
        $this->passwordFailureCallback = $callback;

        return $this;
    }

    /**
     * Sets the callback for handling successful extractions.
     *
     * @param callable $callback Callback for handling successful extractions.
     *
     * @return $this For method chaining.
     */
    public function onSuccess(callable $callback): self
    {
        $this->successCallback = $callback;

        return $this;
    }

    /**
     * Extracts the archive to the specified location.
     *
     * This method performs the extraction and handles exceptions and callbacks for failure or success.
     *
     * @param string      $filePath    Path to the archive.
     * @param string|null $destination Directory to extract to. If not specified, uses the same directory as the archive.
     *
     * @throws \Exception If the password provider is not set.
     *
     * @return mixed Result of the success callback or password failure callback.
     */
    public function extract(string $filePath, ?string $destination = null): mixed
    {
        try {
            $success = $this->performExtraction($filePath, $destination);
        } catch (\Throwable $throwable) {
            return call_user_func($this->failureCallback, $throwable, $filePath, $destination);
        }

        $callback = $success ? $this->successCallback : $this->passwordFailureCallback;

        // Call the appropriate callback based on extraction result.
        return $callback($filePath, $destination);
    }

    /**
     * Performs the extraction using all added handlers.
     *
     * @param string      $filePath    Path to the archive.
     * @param string|null $destination Directory to extract to. If not specified, uses the same directory as the archive.
     *
     * @return bool Result of the extraction.
     */
    private function performExtraction(string $filePath, ?string $destination = null): bool
    {
        $destination = $destination ?: dirname($filePath);

        // Create destination directory if it does not exist
        // $this->ensureDirectoryExists($destination);

        $supportHandlers = array_filter($this->adapters, fn (ArchiveAdapterInterface $archive) => $archive->canSupport($filePath));

        // Attempt extraction with all added handlers.
        foreach ($supportHandlers as $handler) {
            if ($handler->extract($filePath, $destination, $this->passwordProvider)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Ensures that the directory exists. Creates it if it does not.
     *
     * @param string $directory Path to the directory.
     *
     * @throws RuntimeException If the directory could not be created.
     */
    protected function ensureDirectoryExists(string $directory): void
    {
        if (! is_dir($directory) && ! mkdir($directory, 0777, true) && ! is_dir($directory)) {
            throw new RuntimeException("Failed to create directory: {$directory}");
        }
    }
}
