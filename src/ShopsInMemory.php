<?php declare(strict_types=1);

namespace App;

final class ShopsInMemory implements Shops
{
    /**
     * @var Shop[]
     */
    private array $shops = [];

    public function getById(string $id): ?Shop
    {
        foreach ($this->shops as $shop) {
            if ($shop->getId() === $id) {
                return $shop;
            }
        }

        return null;
    }

    public function persist(Shop $shop): void
    {
        $this->shops = array_filter($this->shops, fn ($existing) => $existing->getId() !== $shop->getId());
        $this->shops[] = $shop;
    }
}
