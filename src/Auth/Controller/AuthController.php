<?php

namespace App\Auth\Controller;

use App\Auth\Entity\User;
use App\Auth\Form\LoginType;
use App\Auth\Form\RegistrationType;
use App\Auth\Repository\UserRepository;
use App\Auth\Service\AuthService;
use App\Product\Service\ProductService;
use App\Shop\Service\ShopService;
use App\Shared\Enum\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class AuthController extends AbstractController
{   
    public function __construct(
        private readonly ProductService $productService,
        private readonly ShopService $shopService
    ) {}

   #[Route('/', name: 'landing_page')]
    public function home(): Response
    {
        $user = $this->getUser();
        
        // limit to 6 featured products
        $featuredProducts = array_slice($this->productService->list(), 0, 6);
        
        $categories = Category::cases();
        
        // limlit to 4 featured shops
        $featuredShops = array_slice($this->shopService->listAll(), 0, 4);
        
        return $this->render('home/index.html.twig', [
            'user' => $user,
            'featuredProducts' => $featuredProducts,
            'categories' => $categories,
            'featuredShops' => $featuredShops,
        ]);
    }
    
    #[Route('/login', name: 'login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        
        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $this->createForm(LoginType::class);

        return $this->render('auth/index.html.twig', [
            'loginForm' => $form->createView(),
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }   
    
    #[Route('/register', name: 'register', methods: ['GET', 'POST'])]
    public function register(Request $request, AuthService $authService, UserRepository $userRepository): Response
    {
        $user = new User();
        
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
