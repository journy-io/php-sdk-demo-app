<?php declare(strict_types=1);

namespace App;

use InvalidArgumentException;
use Money\Money;

final class Product
{
    private bool $deleted = false;
    private string $id;
    private string $shopId;
    private string $name;
    private Money $price;

    public function __construct(string $id, string $shopId, string $name, Money $price)
    {
        if (empty($id)) {
            throw new InvalidArgumentException("ID cannot be empty");
        }

        if (empty($shopId)) {
            throw new InvalidArgumentException("Shop ID cannot be empty");
        }

        if (empty($name)) {
            throw new InvalidArgumentException("Name cannot be empty");
        }

        $this->id = $id;
        $this->shopId = $shopId;
        $this->name = $name;
        $this->price = $price;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getShopId(): string
    {
        return $this->shopId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function delete(): void
    {
        $this->deleted = true;
    }
}
