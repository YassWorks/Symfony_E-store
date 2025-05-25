<?php

namespace App\Auth\Controller;

use App\Auth\Entity\User;
use App\Auth\Form\LoginType;
use App\Auth\Form\RegistrationType;
use App\Auth\Repository\UserRepository;
use App\Auth\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class AuthController extends AbstractController
{   
   #[Route('/', name: 'landing_page')]
    public function home(): Response
    {
        // get the current authenticated user using Symfony's security
        $user = $this->getUser();
        
        return $this->render('home/index.html.twig', [
            'user' => $user,
        ]);
    }    #[Route('/login', name: 'login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        // Create the form
        $form = $this->createForm(LoginType::class);

        return $this->render('auth/index.html.twig', [
            'loginForm' => $form->createView(),
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }    #[Route('/register', name: 'register', methods: ['GET', 'POST'])]
    public function register(Request $request, AuthService $authService, UserRepository $userRepository): Response
    {
        $user = new User();
        
        // Create the form using the RegistrationType
        $form = $this->createForm(RegistrationType::class, $user);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $existingUser = $userRepository->findOneBy(['email' => $email]);

            if ($existingUser) {
                $this->addFlash('error', 'A user with this email already exists.');
            } else {
                $authService->registerUser($user, $form);
                return $this->redirectToRoute('login');
            }
        } 
        elseif ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'Please check your input.');
        }
        
        return $this->render('auth/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/logout', name: 'logout', methods: ['GET'])]
    public function logout(): never
    {
        // This method should never be reached
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on the firewall.');
    }
}
