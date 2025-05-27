<?php

namespace App\Admin\Controller;

use App\Admin\Form\AdminPasskeyType;
use App\Admin\Service\AdminService;
use App\Auth\Entity\User;
use App\Auth\Repository\UserRepository;
use App\Shop\Entity\Shop;
use App\Shop\Repository\ShopRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class AdminController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ShopRepository $shopRepository,
        private readonly AdminService $adminService
    ) {}

    #[Route('/admin', name: 'admin')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('login');
        }
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('register_admin');
        }

        return $this->render('admin/index.html.twig');
    }

    #[Route('/register_admin', name: 'register_admin', methods: ['GET', 'POST'])]
    public function registerAdmin(
        Request $request, 
        AdminService $adminService,
        TokenStorageInterface $tokenStorage
    ): Response {
        $user = $this->getUser();
        
        // redirect to login if not authenticated
        if (!$user) {
            return $this->redirectToRoute('login');
        }
        
        // redirect to admin dashboard if already admin
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin');
        }

        $form = $this->createForm(AdminPasskeyType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $passkey = $form->get('passkey')->getData();
            
            if ($adminService->validatePasskey($passkey)) {
                $adminService->promoteToAdmin($user);
                
                // refresh the security token with updated user roles
                // this part is needed to ensure the user is recognized as an admin
                $token = new UsernamePasswordToken(
                    $user,
                    'main',
                    $user->getRoles()
                );
                $tokenStorage->setToken($token);
                
                return $this->redirectToRoute('admin');
            } else {
                $this->addFlash('error', 'Invalid passkey. Please try again.');
            }
        }
        
        return $this->render('admin/register.html.twig', [
            'passkeyForm' => $form->createView(),
        ]);
    }

    #[Route('/admin/users', name: 'admin_users')]
    #[IsGranted('ROLE_ADMIN')]
    public function manageUsers(): Response
    {
        $users = $this->userRepository->findAll();
        
        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/users/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteUser(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            // Prevent admin from deleting themselves
            if ($user === $this->getUser()) {
                $this->addFlash('error', 'You cannot delete your own account.');
            } else {
                $this->adminService->deleteUser($user);
                $this->addFlash('success', 'User has been deleted successfully.');
            }
        }

        return $this->redirectToRoute('admin_users');
    }

    #[Route('/admin/shops', name: 'admin_shops')]
    #[IsGranted('ROLE_ADMIN')]
    public function manageShops(): Response
    {
        $shops = $this->shopRepository->findAll();
        
        return $this->render('admin/shops.html.twig', [
            'shops' => $shops,
        ]);
    }

    #[Route('/admin/shops/{id}/delete', name: 'admin_shop_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteShop(Request $request, Shop $shop): Response
    {
        if ($this->isCsrfTokenValid('delete' . $shop->getId(), $request->request->get('_token'))) {
            $this->adminService->deleteShop($shop);
            $this->addFlash('success', 'Shop has been deleted successfully.');
        }

        return $this->redirectToRoute('admin_shops');
    }
}
