<?php

declare(strict_types=1);

namespace ShopManager\Users;

use InvalidArgumentException;

final class UserId
{
    private string $id;

    public function __construct(string $id)
    {
        if (empty($id)) {
            throw new InvalidArgumentException("User ID cannot be empty!");
        }

        $this->id = $id;
    }

    public function equals(UserId $id): bool
    {
        return $this->id === $id->id;
    }

    public function __toString(): string
    {
        return $this->id;
    }
}
