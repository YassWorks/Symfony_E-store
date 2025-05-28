<?php

namespace App\Cart\Controller;

use App\Cart\Service\CartService;
use App\Product\Entity\Product;
use App\Auth\Entity\User;
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
        private EntityManagerInterface $entityManager
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
    
    // flouci implmentation here -------   
    #[Route('/checkout', name: 'cart_checkout', methods: ['GET'])]
    #[IsGranted('ROLE_BUYER')]
    public function checkout(Request $request)
    {
        $cart = $this->cartService->getCartDetails();
        $session = $request->getSession();
        $crystalDiscount = $session->get('crystal_discount', null);
        
        // Calculate final total
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
        
        // Validate phone number (8 digits)
        if (!preg_match('/^\d{8}$/', $phoneNumber)) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Please enter a valid 8-digit phone number'
            ], 400);
        }
        
        // Check if there's a crystal discount and deduct those crystals
        /** @var User $user */
        $user = $this->getUser();
        $session = $request->getSession();
        $crystalDiscount = $session->get('crystal_discount', null);
        
        if ($crystalDiscount && $crystalDiscount['crystals_used'] > 0) {
            // Deduct crystals used for discount
            $user->subtractCrystals($crystalDiscount['crystals_used']);
            $this->entityManager->persist($user);
        }
        
        // Simulate Flouci transaction delay
        // In real implementation, you would call Flouci API here
        sleep(2); // Simulate processing delay
        
        // Process the order
        $this->processOrder($request);
        
        return new JsonResponse([
            'success' => true,
            'message' => 'Payment processed successfully via Flouci! Your order will be shipped soon.'
        ]);
    }
    
    #[Route('/checkout/crystals', name: 'cart_checkout_crystals', methods: ['POST'])]
    #[IsGranted('ROLE_BUYER')]
    public function checkoutCrystals(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $cart = $this->cartService->getCartDetails();
        $session = $request->getSession();
        $crystalDiscount = $session->get('crystal_discount', null);
        
        // Calculate final total
        $finalTotal = $cart->getTotal();
        if ($crystalDiscount) {
            $finalTotal = $crystalDiscount['new_total'];
        }
        
        // Calculate crystals needed for payment (100 crystals = $1)
        $crystalsNeededForPayment = ceil($finalTotal * 100);
        
        // Calculate total crystals needed (payment + discount if any)
        $totalCrystalsNeeded = $crystalsNeededForPayment;
        if ($crystalDiscount && $crystalDiscount['crystals_used'] > 0) {
            $totalCrystalsNeeded += $crystalDiscount['crystals_used'];
        }
        
        // Check if user has enough crystals for the total
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
        
        // Deduct all crystals needed (payment + discount)
        $user->subtractCrystals($totalCrystalsNeeded);        
        $this->entityManager->persist($user);
        
        // Process the order
        $this->processOrder($request);
        
        return new JsonResponse([
            'success' => true,
            'message' => sprintf(
                'Payment processed successfully! %d crystals were used (equivalent to $%.2f)',
                $totalCrystalsNeeded,
                $cart->getTotal()
            ),
            'crystals_used' => $totalCrystalsNeeded
        ]);
    }    
    
    private function processOrder(Request $request): void
    {
        /** @var User $user */
        $user = $this->getUser();
        $cart = $this->cartService->getCartDetails();
        $session = $request->getSession();
        $crystalDiscount = $session->get('crystal_discount', null);
        
        // Decrease stock for each product
        foreach ($cart->getItems() as $item) {
            $product = $item->getProduct();
            $product->adjustStock(-$item->getQuantity());
            $this->entityManager->persist($product);
        }
        
        // Clear crystal discount from session since order is complete
        if ($crystalDiscount) {
            $session->remove('crystal_discount');
        }
        
        // Clear cart after successful order
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

    #[Route('/apply-crystal-discount', name: 'cart_apply_crystal_discount', methods: ['POST'])]
    #[IsGranted('ROLE_BUYER')]
    public function applyCrystalDiscount(Request $request): JsonResponse
    {
        $crystalsToUse = (int)$request->request->get('crystals', 0);
        /** @var User $user */
        $user = $this->getUser();
        $cart = $this->cartService->getCartDetails();
          // Validation
        if ($crystalsToUse < 0) {
            return new JsonResponse(['success' => false, 'error' => 'Cannot use negative crystals'], 400);
        }
        
        if ($crystalsToUse > $user->getCrystals()) {
            return new JsonResponse([
                'success' => false, 
                'error' => sprintf('You only have %d crystals available', $user->getCrystals())
            ], 400);
        }
        
        // Calculate continuous discount: 1 crystal = 0.01% discount
        $discountPercent = $crystalsToUse * 0.01;
        $originalTotal = $cart->getTotal();
        $discountAmount = ($originalTotal * $discountPercent) / 100;
        $newTotal = $originalTotal - $discountAmount;
        
        // Store discount in session for checkout process
        $session = $request->getSession();
        $session->set('crystal_discount', [
            'crystals_used' => $crystalsToUse,
            'discount_percent' => $discountPercent,
            'discount_amount' => $discountAmount,
            'original_total' => $originalTotal,
            'new_total' => $newTotal
        ]);
          return new JsonResponse([
            'success' => true,
            'crystals_used' => $crystalsToUse,
            'discount_percent' => number_format($discountPercent, 2),
            'discount_amount' => number_format($discountAmount, 2),
            'new_total' => number_format($newTotal, 2)
        ]);
    }

    #[Route('/checkout/apply-discount', name: 'cart_checkout_apply_discount', methods: ['POST'])]
    #[IsGranted('ROLE_BUYER')]
    public function applyDiscountCheckout(Request $request): JsonResponse
    {
        $crystalsToUse = (int)$request->request->get('crystals', 0);
        /** @var User $user */
        $user = $this->getUser();
        $cart = $this->cartService->getCartDetails();
          // Validation
        if ($crystalsToUse < 0) {
            return new JsonResponse(['success' => false, 'error' => 'Cannot use negative crystals'], 400);
        }
        
        if ($crystalsToUse > $user->getCrystals()) {
            return new JsonResponse([
                'success' => false, 
                'error' => sprintf('You only have %d crystals available', $user->getCrystals())
            ], 400);
        }
          // Calculate continuous discount: 1 crystal = 0.01% discount
        $discountPercent = $crystalsToUse * 0.01;
        $originalTotal = $cart->getTotal();
        $discountAmount = ($originalTotal * $discountPercent) / 100;
        $newTotal = $originalTotal - $discountAmount;
        
        // Store discount in session for checkout process
        $session = $request->getSession();
        $session->set('crystal_discount', [
            'crystals_used' => $crystalsToUse,
            'discount_percent' => $discountPercent,
            'discount_amount' => $discountAmount,
            'original_total' => $originalTotal,
            'new_total' => $newTotal
        ]);
          return new JsonResponse([
            'success' => true,
            'crystals_used' => $crystalsToUse,
            'discount_percent' => number_format($discountPercent, 2),
            'discount_amount' => number_format($discountAmount, 2),
            'new_total' => number_format($newTotal, 2),
            'crystals_needed_for_payment' => ceil($newTotal * 100)
        ]);
    }
}
