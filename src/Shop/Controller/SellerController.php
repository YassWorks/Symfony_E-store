<?php

namespace App\Shop\Controller;

use App\Shop\Entity\Shop;
use App\Shop\Service\SellerService;
use App\Shop\Service\ShopService;
use App\Shared\Enum\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Order\Service\OrderService;
use App\Review\Service\ReviewService;

final class SellerController extends AbstractController
{
    public function __construct(private readonly ShopService $shopService)
    {
    }

    #[Route('/join_us', name: 'join_us', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        SellerService $sellerService
    ): Response {
        $shop = new Shop();
    
        $categoryChoices = [];
        foreach (Category::cases() as $category) {
            $categoryChoices[$category->name] = $category->value;
        }
        
        $form = $this->createFormBuilder($shop)
            ->add('name', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Shop Name'
            ])
            ->add('categories', ChoiceType::class, [
                'choices' => $categoryChoices,
                'multiple' => true,
                'expanded' => true,
                'mapped' => false,
                'label' => 'Shop Categories'
            ])
            ->add('website', UrlType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Website URL'
            ])
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Contact Email'
            ])
            ->add('logo', FileType::class, [
                'label' => 'Shop Logo (PNG or JPG file)',
                'mapped' => false,
                'required' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Register Shop',
                'attr' => ['class' => 'btn btn-primary mt-3']
            ])
            ->getForm();
            
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();

            $shop->setOwner($user); // Set the owner of the shop

            $success = $sellerService->registerShop($shop, $form, $user);
            
            if ($success) {
                return $this->redirectToRoute('seller_dashboard');
            } else {
                $this->addFlash('error', 'There was a problem uploading your logo. Please try again.');
            }
        }
        elseif ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'Please check your input and try again.');
        }
          return $this->render('seller/index.html.twig', [
            'shopForm' => $form->createView(),
        ]);
    }    #[Route('/dashboard', name: 'seller_dashboard', methods: ['GET'])]
    public function dashboard(ReviewService $ReviewService, OrderService $orderService): Response
    {
        $user = $this->getUser();
        $shop = $this->shopService->getShopByUser($user);
        $products = [];
        $averageRatings = [];
        $recentOrders = [];
        
        if ($shop) {
            $products = $shop->getProducts();
            
            $productIds = array_map(fn($p) => $p->getId(), $products->toArray());
            $averageRatings = $ReviewService->getProdsAvg($productIds);
            
            // Get recent orders for this shop
            $recentOrders = $orderService->getRecentOrdersForShop($shop, 10);
        }

        return $this->render('seller/dashboard.html.twig', [
            'products' => $products,
            'shop' => $shop,
            'averageRatings' => $averageRatings,
            'recentOrders' => $recentOrders
        ]);
    }
}
