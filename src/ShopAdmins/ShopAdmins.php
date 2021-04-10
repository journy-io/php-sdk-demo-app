<?php declare(strict_types=1);

namespace ShopManager\ShopAdmins;

use ShopManager\Shops\ShopId;
use ShopManager\Users\UserId;

interface ShopAdmins
{
    /**
     * @param UserId $userId
     *
     * @return ShopAdmin[]
     */
    public function getByUserId(UserId $userId): array;

    /**
     * @param ShopId $shopId
     *
     * @return ShopAdmin[]
     */
    public function getByShopId(ShopId $shopId): array;
    public function persist(ShopAdmin $admin): void;
}
