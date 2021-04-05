<?php declare(strict_types=1);

namespace App;

interface ShopAdmins
{
    /**
     * @param string $userId
     *
     * @return ShopAdmin[]
     */
    public function getByUserId(string $userId): array;

    /**
     * @param string $shopId
     *
     * @return ShopAdmin[]
     */
    public function getByShopId(string $shopId): array;
    public function persist(ShopAdmin $admin): void;
}
