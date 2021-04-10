<?php declare(strict_types=1);

namespace ShopManager\Users;

use InvalidArgumentException;

final class Email
{
    private string $email;

    public function __construct(string $email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException("Email is invalid: {$email}");
        }

        $this->email = $email;
    }

    public function equals(Email $email): bool
    {
        return strtolower($this->email) === strtolower($email->email);
    }

    public function __toString(): string
    {
        return $this->email;
    }
}
