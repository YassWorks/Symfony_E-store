<?php
namespace App\Product\Controller;

use App\Product\Entity\Image;
use App\Product\Entity\Product;
use App\Product\Form\ProductType;
use App\Product\Service\ProductService;
use App\Shared\Utils\FileUploader;
use App\Shop\Service\ShopService;
use App\Cart\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Shared\Enum\Category;
use App\Wishlist\Service\WishlistService;
use App\Review\Service\ReviewService;
use App\Review\Form\ReviewType;

#[Route('/products')]
class ProductController extends AbstractController
{    public function __construct(
        private readonly ProductService $service,
        private readonly FileUploader $uploader,
        private readonly ShopService $shopService,
        private readonly ReviewService $reviewService,
        private readonly CartService $cartService
    ) {}    
    #[Route('', name: 'product_index', methods: ['GET'])]
    public function index(Request $request, WishlistService $wishlistService): Response
    {   
        $qRaw = $request->query->get('q', '');
        $minRaw = $request->query->get('minPrice', '');
        $maxRaw = $request->query->get('maxPrice', '');
        $shopRaw = $request->query->get('shop', '');
        $catsRaw = $request->query->all('categories');

        if (!is_array($catsRaw)) {
            $catsRaw = [$catsRaw];
        }
                
        $criteria = [
            'q'=> trim($qRaw),
            'minPrice'=> is_numeric($minRaw) ? (float) $minRaw : null,
            'maxPrice'=> is_numeric($maxRaw) ? (float) $maxRaw : null,
            'shop'=> ctype_digit((string)$shopRaw) ? (int) $shopRaw : null,
            'categories'=> is_array($catsRaw) ? $catsRaw : [],
        ];

        $allShops = $this->shopService->listAll();
        $allCategories = Category::cases();
        $products = $this->service->findByFilters($criteria);
        $wishlist = $wishlistService->getOrCreateByUser($this->getUser());
        
        // Get cart quantities for all products
        $cartQuantities = [];
        foreach ($products as $product) {
            $cartQuantities[$product->getId()] = $this->cartService->getProductQuantityInCart($product->getId());
        }
        
        return $this->render('product/index.html.twig', [
                'products'=> $products,
                'allShops'=> $allShops,
                'allCategories' => $allCategories,
                'criteria'=> $criteria,
                'wishlist'=> $wishlist,
                'cartQuantities' => $cartQuantities
            ]);
    }

    #[Route('/new', name: 'product_new', methods: ['GET','POST'])]
    #[IsGranted('ROLE_SELLER')]
    public function new(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        if ($form->isSubmitted() && $form->isValid()) {
            // handle image uploads
            $files = $form->get('images')->getData();
            foreach ((array)$files as $file) {
                if ($file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                    $result = $this->uploader->uploadFile($file, "/products_images/");
                    if ($result['success']) {
                        $img = new Image();
                        $img->setFilename($result['filename']);
                        $product->addImage($img);
                    }
                }
            }

            // Associate product with the current user's shop
            $user = $this->getUser();
            $shop = $this->shopService->getShopByUser($user);
            if ($shop) {
                $product->setShop($shop);
            } else {
                // Handle case where seller does not have a shop
                $this->addFlash('error', 'You must register a shop before adding products.');
                return $this->redirectToRoute('join_us'); // Or some other appropriate route
            }

            $this->service->save($product);
            return $this->redirectToRoute('seller_dashboard'); // Redirect to seller dashboard
        }

        return $this->render('product/new.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/{id}/edit', name: 'product_edit', methods: ['GET','POST'])]
    #[IsGranted('ROLE_SELLER')]
    public function edit(Request $request, Product $product): Response
    {
        // Check if the current user owns the product's shop
        $user = $this->getUser();
        if (!$product->getShop() || $product->getShop()->getOwner() !== $user) {
            $this->addFlash('error', 'You are not authorized to edit this product.');
            return $this->redirectToRoute('seller_dashboard');
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);        if ($form->isSubmitted() && $form->isValid()) {
            $files = $form->get('images')->getData();
            foreach ((array)$files as $file) {
                if ($file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                    $result = $this->uploader->uploadFile($file, "/products_images/");
                    if ($result['success']) {
                        $img = new Image();
                        $img->setFilename($result['filename']);
                        $product->addImage($img);
                    }
                }
            }

            $this->service->save($product);
            return $this->redirectToRoute('seller_dashboard'); // Redirect to seller dashboard
        }

        return $this->render('product/edit.html.twig', [
            'form' => $form->createView(),
            'product' => $product
        ]);
    }

    #[Route('/{id}', name: 'product_show', methods: ['GET'])]
    public function add(int $id, Request $request): Response
    {
        $product = $this->service->get($id);
        $reviews = $this->reviewService->getReviewsForProduct($id);
        if (!$product) {
            throw $this->createNotFoundException('Produit introuvable');
        }

        $existingReview = $this->reviewService->findReviewByUserAndProduct(
            $this->getUser(),
            $product
        );

        $form = $this->createForm(ReviewType::class, $existingReview);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->reviewService->saveReview(
                $this->getUser(),
                $product,
                $form->get('rating')->getData(),
                $form->get('comment')->getData()
            );

            return $this->redirectToRoute('product_show', ['id' => $id]);
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'form'=> $form->createView(),
            'reviews'=> $reviews,
        ]);
    }

    #[Route('/{id}', name: 'product_delete', methods: ['POST'])]
    #[IsGranted('ROLE_SELLER')]
    public function delete(Request $request, Product $product): Response
    {
        // Check if the current user owns the product's shop
        $user = $this->getUser();
        if (!$product->getShop() || $product->getShop()->getOwner() !== $user) {
            $this->addFlash('error', 'You are not authorized to delete this product.');
            return $this->redirectToRoute('seller_dashboard');
        }

        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            $this->service->delete($product);
        }
        return $this->redirectToRoute('seller_dashboard'); // Redirect to seller dashboard
    }
}