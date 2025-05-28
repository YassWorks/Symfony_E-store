<?php

namespace App\Core\Controller;

use App\Core\Service\ProfileService;
use App\Shop\Service\ShopService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'profile')]
    public function index(ProfileService $profileService, ShopService $shopService): Response
    {
        $user = $this->getUser();
        $userShop = null;
        
        if ($user) {
            $userShop = $shopService->getShopByUser($user);
        }
        
        return $this->render('profile/index.html.twig', [
            'userShop' => $userShop,
            'shop_service' => $shopService
        ]);
    }
}
