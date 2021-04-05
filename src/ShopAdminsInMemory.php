<?php declare(strict_types=1);

namespace App;

final class ShopAdminsInMemory implements ShopAdmins
{
    /**
     * @var ShopAdmin[]
     */
    private array $admin = [];

    public function getByUserId(string $userId): array
    {
        return array_filter($this->admin, fn ($admin) => $admin->getUserId() === $userId);
    }

    public function getByShopId(string $shopId): array
    {
        return array_filter($this->admin, fn ($admin) => $admin->getShopId() === $shopId);
    }

    public function persist(ShopAdmin $admin): void
    {
        $this->admin = array_filter(
            $this->admin,
            fn ($existing) => !($existing->getUserId() === $admin->getUserId() && $existing->getShopId() === $admin->getShopId())
        );
        $this->admin[] = $admin;
    }
}
