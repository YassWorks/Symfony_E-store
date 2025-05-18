<?php
namespace App\Cart\Controller;

use App\Cart\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
    public function add(Request $req, int $id)
    {
        $qty = (int)$req->request->get('quantity', 1);
        $this->cartService->addProduct($id, $qty);
        return $this->redirectToRoute('cart_index');
    }

    #[Route('/remove/{itemId}', name: 'cart_remove', methods: ['POST'])]
    #[IsGranted('ROLE_BUYER')]
    public function remove(int $itemId)
    {
        $this->cartService->removeProduct($itemId);
        return $this->redirectToRoute('cart_index');
    }

    #[Route('/checkout', name: 'cart_checkout', methods: ['GET','POST'])]
    #[IsGranted('ROLE_BUYER')]
    public function checkout()
    {
        //convert cart to order here
        $this->addFlash('success','Order placed!');
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
}

?>