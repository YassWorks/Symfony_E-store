<?php

namespace App\Admin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Shared\Enum\Role;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        if (!$this->isGranted(Role::ROLE_ADMIN)) {
            return $this->redirectToRoute('register_admin');
        }

        return $this->render('admin/index.html.twig');
    }

    #[Route('/register_admin', name: 'register_admin')]
    public function registerAdmin(): Response
    {
        $user = $this->getUser();
        if ($user && !$this->isGranted(Role::ROLE_ADMIN)) {
            return $this->redirectToRoute('admin');
        }
        else if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        return $this->render('admin/register_admin.html.twig');
    }
}