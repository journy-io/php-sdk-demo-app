<?php

declare(strict_types=1);

namespace ShopManager\Shops;

final class ShopsInMemory implements Shops
{
    /**
     * @var Shop[]
     */
    private array $shops = [];

    public function getById(ShopId $id): ?Shop
    {
        foreach ($this->shops as $shop) {
            if ($shop->getId()->equals($id)) {
                return $shop;
            }
        }

        return null;
    }

    public function persist(Shop $shop): void
    {
        $this->shops = array_values(
            array_filter($this->shops, fn ($existing) => $existing->getId()->equals($shop->getId()) === false)
        );
        $this->shops[] = $shop;
    }
}
