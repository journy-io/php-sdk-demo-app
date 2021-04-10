<?php

declare(strict_types=1);

namespace ShopManager\ShopAdmins;

use ShopManager\Shops\ShopId;
use ShopManager\Users\UserId;

final class ShopAdminsInMemory implements ShopAdmins
{
    /**
     * @var ShopAdmin[]
     */
    private array $admin = [];

    public function getByUserId(UserId $userId): array
    {
        return array_values(
            array_filter($this->admin, fn ($admin) => $admin->getUserId()->equals($userId))
        );
    }

    public function getByShopId(ShopId $shopId): array
    {
        return array_values(
            array_filter($this->admin, fn ($admin) => $admin->getShopId()->equals($shopId))
        );
    }

    public function persist(ShopAdmin $admin): void
    {
        $this->admin = array_values(
            array_filter(
                $this->admin,
                fn ($existing) => !($existing->getUserId()->equals($admin->getUserId()) && $existing->getShopId()->equals($admin->getShopId()))
            )
        );
        $this->admin[] = $admin;
    }
}
