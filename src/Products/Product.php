<?php declare(strict_types=1);

namespace ShopManager\Products;

use ShopManager\Shops\ShopId;
use InvalidArgumentException;
use Money\Money;

final class Product
{
    private bool $deleted = false;
    private ProductId $id;
    private ShopId $shopId;
    private string $name;
    private Money $price;

    public function __construct(ProductId $id, ShopId $shopId, string $name, Money $price)
    {
        if (empty($name)) {
            throw new InvalidArgumentException("Name cannot be empty");
        }

        $this->id = $id;
        $this->shopId = $shopId;
        $this->name = $name;
        $this->price = $price;
    }

    public function getId(): ProductId
    {
        return $this->id;
    }

    public function getShopId(): ShopId
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
