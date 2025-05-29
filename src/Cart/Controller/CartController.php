<?php

namespace App\Cart\Controller;

use App\Cart\Service\CartService;
use App\Product\Entity\Product;
use App\Auth\Entity\User;
use App\Order\Service\OrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/cart')]
class CartController extends AbstractController
{
    public function __construct(
        private CartService $cartService,
        private EntityManagerInterface $entityManager,
        private OrderService $orderService
    ) {}

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
        
        // checking if product is available
        if ($product->getStockQuantity() <= 0) {
            return new JsonResponse([
                'success' => false,
                'message' => 'This product is currently out of stock.',
                'stock_status' => 'out_of_stock'
            ], 400);
        }
        
        $currentCartQty = $this->cartService->getProductQuantityInCart($id);
        $newTotalQty = $currentCartQty + $qty;
        
        // check if new total quantity would exceed stock
        if ($newTotalQty > $product->getStockQuantity()) {
            $availableQty = $product->getStockQuantity() - $currentCartQty;
            return new JsonResponse([
                'success' => false,
                'message' => sprintf(
                    'Cannot add %d items. Only %d items available (you already have %d in cart).',
                    $qty,
                    $availableQty,
                    $currentCartQty
                ),
                'available_quantity' => $availableQty,
                'current_cart_quantity' => $currentCartQty,
                'stock_status' => $availableQty > 0 ? 'limited' : 'max_reached'
            ], 400);
        }
        
        $this->cartService->addProduct($id, $qty);
        
        // remaining
        $remainingStock = $product->getStockQuantity() - $newTotalQty;
        
        return new JsonResponse([
            'success' => true,
            'message' => sprintf('%s added to cart successfully!', $product->getTitle()),
            'quantity' => $qty,
            'new_cart_quantity' => $newTotalQty,
            'remaining_stock' => $remainingStock,
            'stock_status' => $remainingStock > 0 ? 'available' : 'max_reached'
        ]);    }

    #[Route('/check-stock/{id}', name: 'cart_check_stock', methods: ['GET'])]
    #[IsGranted('ROLE_BUYER')]
    public function checkStock(Product $product): JsonResponse
    {
        $currentCartQty = $this->cartService->getProductQuantityInCart($product->getId());
        $availableQty = $product->getStockQuantity() - $currentCartQty;
        
        return new JsonResponse([
            'product_id' => $product->getId(),
            'stock_quantity' => $product->getStockQuantity(),
            'current_cart_quantity' => $currentCartQty,
            'available_quantity' => $availableQty,
            'stock_status' => $this->getStockStatus($product->getStockQuantity(), $currentCartQty)
        ]);
    }
    
    private function getStockStatus(int $stockQuantity, int $currentCartQty): string
    {
        if ($stockQuantity <= 0) {
            return 'out_of_stock';
        }
        
        $availableQty = $stockQuantity - $currentCartQty;
        if ($availableQty <= 0) {
            return 'max_reached';
        }
        
        return 'available';
    }

    #[Route('/remove/{itemId}', name: 'cart_remove', methods: ['POST', 'GET'])]
    #[IsGranted('ROLE_BUYER')]
    public function remove(int $itemId)
    {   
        $this->cartService->removeProduct($itemId);
        return $this->redirectToRoute('cart_index');
    }    
    
    // checkout starts here (we made just a mock checkout for Flouci as an example)
    #[Route('/checkout', name: 'cart_checkout', methods: ['GET'])]
    #[IsGranted('ROLE_BUYER')]
    public function checkout(Request $request)
    {
        $cart = $this->cartService->getCartDetails();
        $session = $request->getSession();
        $crystalDiscount = $session->get('crystal_discount', null);
        
        $finalTotal = $cart->getTotal();
        if ($crystalDiscount) {
            $finalTotal = $crystalDiscount['new_total'];
        }
        
        return $this->render('cart/checkout.html.twig', [
            'cart' => $cart,
            'crystal_discount' => $crystalDiscount,
            'final_total' => $finalTotal
        ]);
    }    
    
    #[Route('/checkout/flouci', name: 'cart_checkout_flouci', methods: ['POST'])]
    #[IsGranted('ROLE_BUYER')]    
    public function checkoutFlouci(Request $request): JsonResponse
    {
        $phoneNumber = $request->request->get('phone_number');
        $deliveryAddress = $request->request->get('delivery_address');
        
        // phone num tounsi (8 digits)
        if (!preg_match('/^\d{8}$/', $phoneNumber)) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Please enter a valid 8-digit phone number'
            ], 400);
        }
        
        if (empty($deliveryAddress) || strlen(trim($deliveryAddress)) < 10) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Please enter a valid delivery address (minimum 10 characters)'
            ], 400);
        }

        // remove crystals from discount if any
        /** @var User $user */
        $user = $this->getUser();
        $session = $request->getSession();
        $crystalDiscount = $session->get('crystal_discount', null);
        
        // total
        $cart = $this->cartService->getCartDetails();
        $finalTotal = $cart->getTotal();
        if ($crystalDiscount) {
            $finalTotal = $crystalDiscount['new_total'];
        }
        
        if ($crystalDiscount && $crystalDiscount['crystals_used'] > 0) {
            $user->subtractCrystals($crystalDiscount['crystals_used']);
        }
        
        // award 10 crystals for every $1 spent when paying with Flouci
        $crystalReward = floor($finalTotal * 10);
        $user->addCrystals($crystalReward);
        $this->entityManager->persist($user);
        sleep(2); // simulation of delay :D

        $this->processOrder($request, 'Flouci', $deliveryAddress);
        
        return new JsonResponse([
            'success' => true,
            'message' => sprintf(
                'Payment processed successfully via Flouci! Your order will be shipped soon. You earned %d crystals!',
                $crystalReward
            )
        ]);
    }
    
    #[Route('/checkout/crystals', name: 'cart_checkout_crystals', methods: ['POST'])]
    #[IsGranted('ROLE_BUYER')]    
    public function checkoutCrystals(Request $request): JsonResponse
    {
        $deliveryAddress = $request->request->get('delivery_address');
        
        // validate delivery address
        if (empty($deliveryAddress) || strlen(trim($deliveryAddress)) < 10) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Please enter a valid delivery address (minimum 10 characters)'
            ], 400);
        }
        
        /** @var User $user */
        $user = $this->getUser();
        $cart = $this->cartService->getCartDetails();
        $session = $request->getSession();
        $crystalDiscount = $session->get('crystal_discount', null);
        
        // total
        $finalTotal = $cart->getTotal();
        if ($crystalDiscount) {
            $finalTotal = $crystalDiscount['new_total'];
        }
        
        $crystalsNeededForPayment = ceil($finalTotal * 100);
        
        // crystals total
        $totalCrystalsNeeded = $crystalsNeededForPayment;
        if ($crystalDiscount && $crystalDiscount['crystals_used'] > 0) {
            $totalCrystalsNeeded += $crystalDiscount['crystals_used'];
        }
        
        // check if user has enough
        if ($user->getCrystals() < $totalCrystalsNeeded) {
            return new JsonResponse([
                'success' => false,
                'error' => sprintf(
                    'Insufficient crystals. You need %d crystals total (%d for payment + %d for discount) but only have %d crystals.',
                    $totalCrystalsNeeded,
                    $crystalsNeededForPayment,
                    $crystalDiscount ? $crystalDiscount['crystals_used'] : 0,
                    $user->getCrystals()
                )
            ], 400);
        }
        $user->subtractCrystals($totalCrystalsNeeded);        
        $this->entityManager->persist($user);

        $this->processOrder($request, 'Crystals', $deliveryAddress);
        
        return new JsonResponse([
            'success' => true,
            'message' => sprintf(
                'Payment processed successfully! %d crystals were used.',
                $totalCrystalsNeeded,
                $cart->getTotal()
            ),
            'crystals_used' => $totalCrystalsNeeded
        ]);
    }    
    
    private function processOrder(Request $request, string $paymentMethod, string $deliveryAddress): void
    {
        /** @var User $user */
        $user = $this->getUser();
        $cart = $this->cartService->getCartDetails();
        $session = $request->getSession();
        $crystalDiscount = $session->get('crystal_discount', null);
        
        $finalTotal = $cart->getTotal();
        $crystalsUsed = 0;
        if ($crystalDiscount) {
            $finalTotal = $crystalDiscount['new_total'];
            $crystalsUsed = $crystalDiscount['crystals_used'] ?? 0;
        }
        
        if ($paymentMethod === 'Crystals') {
            $crystalsUsed += ceil($finalTotal * 100); // 100 crystals = $1
        }
        
        // creating order here (cuz might need later idk)
        $order = $this->orderService->createOrderFromCart(
            $user,
            $cart,
            $paymentMethod,
            $deliveryAddress,
            $crystalsUsed > 0 ? $crystalsUsed : null
        );
        
        // decrease stock
        foreach ($cart->getItems() as $item) {
            $product = $item->getProduct();
            $product->adjustStock(-$item->getQuantity());
            $this->entityManager->persist($product);
        }
        
        if ($crystalDiscount) {
            $session->remove('crystal_discount');
        }
        
        // empty cart
        foreach ($cart->getItems() as $item) {
            $cart->removeItem($item);
            $this->entityManager->remove($item);
        }
        
        $this->entityManager->flush();
    }
    
    #[Route('/update/{itemId}', name: 'cart_update', methods: ['POST'])]
    #[IsGranted('ROLE_BUYER')]
    public function updateQuantity(Request $request, int $itemId): Response
    {
        $qty = (int)$request->request->get('quantity', 1);
        
        try {
            $this->cartService->updateQuantity($itemId, $qty);
            $this->addFlash('success', 'Quantity updated successfully!');
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('cart_index');
    }
    
    #[Route('/update-all', name: 'cart_update_all', methods: ['POST'])]
    #[IsGranted('ROLE_BUYER')]
    public function updateAllQuantities(Request $request): Response
    {
        $quantities = $request->request->all('quantities');

        $validQuantities = [];
        foreach ($quantities as $itemId => $quantity) {
            $qty = (int)$quantity;
            if ($qty >= 0) {
                $validQuantities[(int)$itemId] = $qty;
            }
        }
        
        try {
            $this->cartService->updateAllQuantities($validQuantities);
            $this->addFlash('success', 'Cart updated successfully!');
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        }
        
        return $this->redirectToRoute('cart_index');
    }
    
    #[Route('/checkout/apply-discount', name: 'cart_checkout_apply_discount', methods: ['POST'])]
    #[Route('/apply-crystal-discount', name: 'cart_apply_crystal_discount', methods: ['POST'])]
    #[IsGranted('ROLE_BUYER')]
    public function applyCrystalDiscount(Request $request): JsonResponse
    {
        $crystalsToUse = (int)$request->request->get('crystals', 0);
        /** @var User $user */
        $user = $this->getUser();
        $cart = $this->cartService->getCartDetails();

        if ($crystalsToUse < 0) {
            return new JsonResponse(['success' => false, 'error' => 'Cannot use negative crystals'], 400);
        }
        
        if ($crystalsToUse > $user->getCrystals()) {
            return new JsonResponse([
                'success' => false, 
                'error' => sprintf('You only have %d crystals available', $user->getCrystals())
            ], 400);
        }
        
        // calculate discount: 1 crystal = 0.01% discount
        $discountPercent = $crystalsToUse * 0.01;
        $originalTotal = $cart->getTotal();
        $discountAmount = ($originalTotal * $discountPercent) / 100;
        $newTotal = $originalTotal - $discountAmount;
        
        $session = $request->getSession();
        $session->set('crystal_discount', [
            'crystals_used' => $crystalsToUse,
            'discount_percent' => $discountPercent,
            'discount_amount' => $discountAmount,
            'original_total' => $originalTotal,
            'new_total' => $newTotal
        ]);

        $response = [
            'success' => true,
            'crystals_used' => $crystalsToUse,
            'discount_percent' => number_format($discountPercent, 2),
            'discount_amount' => number_format($discountAmount, 2),
            'new_total' => number_format($newTotal, 2)
        ];
        
        // add payment info if this is the checkout route (there were 2 functions but we merged them)
        if ($request->attributes->get('_route') === 'cart_checkout_apply_discount') {
            $response['crystals_needed_for_payment'] = ceil($newTotal * 100);
        }
        
        return new JsonResponse($response);
    }

    // adding a button to checkout to clear the discount for convenience (instead of going back to cart)
    #[Route('/clear-crystal-discount', name: 'cart_clear_crystal_discount', methods: ['POST'])]
    #[IsGranted('ROLE_BUYER')]
    public function clearCrystalDiscount(Request $request): JsonResponse
    {
        $session = $request->getSession();
        $session->remove('crystal_discount');
        
        $cart = $this->cartService->getCartDetails();
        $originalTotal = $cart->getTotal();
        
        return new JsonResponse([
            'success' => true,
            'message' => 'Crystal discount has been cleared',
            'original_total' => number_format($originalTotal, 2)
        ]);
    }
}
