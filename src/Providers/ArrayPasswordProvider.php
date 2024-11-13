<?php

namespace Esplora\Lumos\Providers;

use Esplora\Lumos\Contracts\PasswordProviderInterface;

/**
 * Password provider that returns passwords from an array.
 *
 * This class implements the PasswordProviderInterface and provides passwords for extracting protected archives
 * from an array passed to the constructor. It is suitable for simple cases where the list of passwords is known
 * beforehand and does not change dynamically.
 */
class ArrayPasswordProvider implements PasswordProviderInterface
{
    /**
     * Constructor for the ArrayPasswordProvider class.
     *
     * @param array $passwords Array of passwords to be used for extracting protected archives.
     */
    public function __construct(protected array $passwords) {}

    /**
     * Returns the list of passwords.
     *
     * This method returns the array of passwords provided in the constructor, which can be used for attempting
     * to extract password-protected archives.
     *
     * @return array Returns an array containing password strings.
     */
    public function getPasswords(): array
    {
        return $this->passwords;
    }
}
