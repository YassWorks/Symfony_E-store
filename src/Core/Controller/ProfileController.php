<?php

namespace App\Core\Controller;

use App\Auth\Form\ProfileEditType;
use App\Core\Service\ProfileService;
use App\Shop\Service\ShopService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Auth\Entity\User;

final class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'profile')]
    #[IsGranted('ROLE_BUYER')]
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

    #[Route('/profile/edit', name: 'profile_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_BUYER')]
    public function edit(Request $request, ProfileService $profileService): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ProfileEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $success = $profileService->updateProfile($user, $form);
            
            if ($success) {
                $this->addFlash('success', 'Your profile has been updated successfully.');
                return $this->redirectToRoute('profile');
            } else {
                $this->addFlash('error', 'This email address is already in use by another account.');
            }
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    #[Route('/profile/delete', name: 'profile_delete', methods: ['POST'])]
    #[IsGranted('ROLE_BUYER')]
    public function delete(Request $request, ProfileService $profileService): Response
    {
        $user = $this->getUser();

        $user = $this->getUser();
        if ($user instanceof User && $this->isCsrfTokenValid('delete_account' . $user->getId(), $request->request->get('_token'))) {
            try {
                $profileService->deleteUserAccount($user);
                
                // Invalidate the session and redirect to login
                $this->container->get('security.token_storage')->setToken(null);
                $request->getSession()->invalidate();
                
                return $this->redirectToRoute('landing_page');
                
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred while deleting your account. Please try again.');
            }
        } else {
            $this->addFlash('error', 'Invalid security token. Please try again.');
        }

        return $this->redirectToRoute('profile');
    }
}
