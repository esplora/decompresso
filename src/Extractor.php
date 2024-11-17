<?php

namespace Esplora\Lumos;

use Esplora\Lumos\Contracts\AdapterInterface;
use Esplora\Lumos\Contracts\PasswordProviderInterface;
use Esplora\Lumos\Providers\ArrayPasswordProvider;
use Illuminate\Support\Collection;

/**
 * Class for extracting files with support for passwords and file adapters.
 *
 * This class manages the extraction process, supporting various file types through adapters and
 * using different passwords via a password provider.
 */
class Extractor
{
    /**
     * Password provider for handling protected files.
     *
     * @var PasswordProviderInterface
     */
    protected PasswordProviderInterface $passwordProvider;

    /**
     * Collection of adapters adapters for extracting files.
     *
     * @var \Illuminate\Support\Collection<AdapterInterface>
     */
    protected Collection $adapters;

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
     * Initializes default adapters for successful and failed extractions.
     */
    public function __construct(iterable $adapters = [])
    {
        // Default password provider with an empty password list.
        $this->passwordProvider = new ArrayPasswordProvider([]);

        // Default callback for failed extraction.
        $this->failureCallback = fn (\Throwable $exception) => false;

        // Default callback for successful extraction.
        $this->successCallback = fn () => true;

        // Default callback for password failure.
        $this->passwordFailureCallback = fn () => false;

        $this->adapters = collect($adapters);
    }

    /**
     * Short hand method to create an Extractor instance with the provided files adapters.
     *
     * @param iterable $adapters iterable of file adapters.
     */
    public static function make(iterable $adapters): self
    {
        return (new static)->withAdapters($adapters);
    }

    /**
     * Sets the password provider for handling protected files.
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
     * Adds an file adapter to support different file formats.
     *
     * @param AdapterInterface $adapter The file adapter.
     *
     * @return $this For method chaining.
     */
    public function withAdapter(AdapterInterface $adapter): self
    {
        $this->adapters->push($adapter);

        return $this;
    }

    /**
     * Adds multiple file adapters.
     *
     * @param AdapterInterface[] $adapters Array of file adapters.
     *
     * @return $this For method chaining.
     */
    public function withAdapters(iterable $adapters): self
    {
        $this->adapters = $this->adapters->merge($adapters);

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
     * Extracts the file to the specified location.
     *
     * This method performs the extraction and handles exceptions and callbacks for failure or success.
     *
     * @param string      $filePath    Path to the file.
     * @param string|null $destination Directory to extract to. If not specified, uses the same directory as the file.
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
     * Performs the extraction using all added adapters.
     *
     * @param string      $filePath    Path to the file.
     * @param string|null $destination Directory to extract to. If not specified, uses the same directory as the file.
     *
     * @return bool Result of the extraction.
     */
    private function performExtraction(string $filePath, ?string $destination = null): bool
    {
        $destination = $destination ?: dirname($filePath);

        $supportAdapters = $this->getSupportedAdapters($filePath);

        // Attempt extraction with all added adapters.
        foreach ($supportAdapters as $adapter) {
            if ($adapter->extract($filePath, $destination, $this->passwordProvider)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $filePath
     *
     * @return Collection
     */
    public function getSupportedAdapters(string $filePath): Collection
    {
        return $this->adapters
            ->filter(fn (AdapterInterface $file) => $file->canSupport($filePath));
    }
}
