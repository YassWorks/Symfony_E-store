<?php

namespace App\Shop\Service;

use App\Auth\Entity\User;
use App\Shop\Entity\Shop;
use App\Shop\Repository\ShopRepository;

class ShopService
{
    public function __construct(private readonly ShopRepository $shopRepository)
    {
    }

    public function getShopByUser(User $user): ?Shop
    {
        return $this->shopRepository->findOneBy(['owner' => $user]);
    }
    /**
     *
     * @return Shop[]
     */
    public function listAll(): array
    {
        return $this->shopRepository->findAll();
    }

}
