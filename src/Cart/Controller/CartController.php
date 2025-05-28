<?php

namespace App\Cart\Controller;

use App\Cart\Service\CartService;
use App\Product\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Response;

#[Route('/cart')]
class CartController extends AbstractController
{
    public function __construct(private CartService $cartService) {}
    #[Route('', name: 'cart_index', methods: ['GET'])]
    #[IsGranted('ROLE_BUYER')]
    public function index()
    {
        $cart = $this->cartService->getCartDetails();
        return $this->render('cart/index.html.twig', compact('cart'));
    }

    #[Route('/add/{id}', name: 'cart_add', methods: ['POST'])]
    #[IsGranted('ROLE_BUYER')]
    public function add(Request $req, int $id, Product $product): Response
    {
        $qty = (int)$req->request->get('quantity', 1);
        $this->cartService->addProduct($id, $qty);

        // Always return JSON response for better UX
        if ($req->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => true,
                'message' => sprintf('%s added to cart successfully!', $product->getTitle()),
                'quantity' => $qty
            ]);
        }

        // For non-AJAX requests, also return JSON to be handled by frontend
        return new JsonResponse([
            'success' => true,
            'message' => sprintf('%s added to cart successfully!', $product->getTitle()),
            'quantity' => $qty,
            'redirect' => false
        ]);
    }

    #[Route('/remove/{itemId}', name: 'cart_remove', methods: ['POST'])]
    #[IsGranted('ROLE_BUYER')]
    public function remove(int $itemId)
    {
        $this->cartService->removeProduct($itemId);
        return $this->redirectToRoute('cart_index');
    }

    #[Route('/checkout', name: 'cart_checkout', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_BUYER')]
    public function checkout()
    {
        return $this->redirectToRoute('product_index');
    }

    #[Route('/update/{itemId}', name: 'cart_update', methods: ['POST'])]
    #[IsGranted('ROLE_BUYER')]
    public function updateQuantity(Request $request, int $itemId): Response
    {
        $qty = (int)$request->request->get('quantity', 1);
        $this->cartService->updateQuantity($itemId, $qty);

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/update-all', name: 'cart_update_all', methods: ['POST'])]
    #[IsGranted('ROLE_BUYER')]
    public function updateAllQuantities(Request $request): Response
    {
        $quantities = $request->request->all('quantities');

        // Convert string values to integers and filter out invalid entries
        $validQuantities = [];
        foreach ($quantities as $itemId => $quantity) {
            $qty = (int)$quantity;
            if ($qty >= 0) { // Allow 0 to remove items
                $validQuantities[(int)$itemId] = $qty;
            }
        }

        $this->cartService->updateAllQuantities($validQuantities);

        return $this->redirectToRoute('cart_index');
    }
}
