<?php

namespace App\Core\Controller;

use App\Core\Service\ProfileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'profile')]
    public function index(ProfileService $profileService): Response
    {
        return $this->render('profile/index.html.twig');
    }
}
