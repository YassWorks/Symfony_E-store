<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\Role;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AuthController extends AbstractController
{   
    #[Route('/', name: 'landing_page')]
    public function landing(): Response
    {
        return $this->render('home/index.html.twig');
    }

    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            
            $user = $userRepository->findOneBy(['email' => $email]);
            
            if ($user && password_verify($password, $user->getPassword())) {
                return $this->redirectToRoute('landing_page');
            }
            
            $this->addFlash('error', 'Wrong credentials please try again');
        }
        
        return $this->render('auth/index.html.twig');
    }

    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');
            
            if ($password === $confirmPassword) {
                $user = new User();
                $user->setName($name);
                $user->setEmail($email);
                $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
                $user->setRole(Role::USER);
                
                $entityManager->persist($user);
                $entityManager->flush();
                
                return $this->redirectToRoute('app_login');
            } else {
                $this->addFlash('error', 'Passwords do not match');
            }
        }
        
        return $this->render('auth/register.html.twig');
    }
}