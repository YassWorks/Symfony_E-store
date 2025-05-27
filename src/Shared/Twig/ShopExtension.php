<?php

namespace App\Shared\Twig;

use App\Auth\Entity\User;
use App\Shop\Service\ShopService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ShopExtension extends AbstractExtension
{
    public function __construct(private readonly ShopService $shopService)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('user_has_shop', [$this, 'userHasShop']),
        ];
    }

    public function userHasShop(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        $shop = $this->shopService->getShopByUser($user);
        return $shop !== null;
    }
}
