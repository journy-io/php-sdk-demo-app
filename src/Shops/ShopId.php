<?php

declare(strict_types=1);

namespace ShopManager\Shops;

use InvalidArgumentException;

final class ShopId
{
    private string $id;

    public function __construct(string $id)
    {
        if (empty($id)) {
            throw new InvalidArgumentException("Shop ID cannot be empty!");
        }

        $this->id = $id;
    }

    public function equals(ShopId $id): bool
    {
        return $this->id === $id->id;
    }

    public function __toString(): string
    {
        return $this->id;
    }
}
