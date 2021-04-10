<?php

declare(strict_types=1);

namespace ShopManager\Shops;

use DateTimeImmutable;
use InvalidArgumentException;

final class Shop
{
    private ShopId $id;
    private string $name;
    private DateTimeImmutable $createdAt;

    public function __construct(ShopId $id, string $name, DateTimeImmutable $createdAt)
    {
        if (empty($name)) {
            throw new InvalidArgumentException("Shop name cannot be empty!");
        }

        $this->id = $id;
        $this->name = $name;
        $this->createdAt = $createdAt;
    }

    public function getId(): ShopId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
