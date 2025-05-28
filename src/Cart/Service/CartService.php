<?php
namespace App\Cart\Service;

use App\Cart\Entity\Cart;
use App\Cart\Entity\CartItem;
use App\Cart\Repository\CartRepository;
use App\Product\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CartService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CartRepository $cartRepo,
        private ProductRepository $productRepo,
        private TokenStorageInterface $tokenStore
    ){}

    private function getCart(): Cart
    {
        $token = $this->tokenStore->getToken();
        $user = $token?->getUser();
        if (! $user || ! is_object($user)) {
            throw new AccessDeniedException('You must be logged in to have a cart.');
        }
        $cart = $this->cartRepo->findOneBy(['user'=>$user]) ?? new Cart();
        if (!$cart->getId()) {
            $cart->setUser($user);
            $this->em->persist($cart);
            $this->em->flush();
        }
        return $cart;
    }

    public function addProduct(int $productId, int $qty = 1): void
    {
        $cart = $this->getCart();
        $product = $this->productRepo->find($productId);
        $existing = $cart->getItems()
            ->filter(fn($i) => $i->getProduct()->getId() === $productId)
            ->first()
        ;
        if ($existing) {
            $existing->setQuantity($existing->getQuantity() + $qty);
        } else {
            $item = new CartItem();
            $item->setProduct($product)->setQuantity($qty);
            $cart->addItem($item);
        }
        $this->em->flush();
    }

    public function removeProduct(int $itemId): void
    {
        $cart = $this->getCart();
        $item = $this->em->find(CartItem::class, $itemId);
        if ($item) {
            $cart->removeItem($item);
            $this->em->flush();
        }
    }

    public function getCartDetails(): Cart
    {
        return $this->getCart();
    }    
    
    public function updateQuantity(int $itemId, int $quantity): void
    {
        $item = $this->em->find(CartItem::class, $itemId);
        if ($item && $quantity > 0) {
            $item->setQuantity($quantity);
        } elseif ($item) {
            // remove if zero or negative
            $cart = $item->getCart();
            $cart->removeItem($item);
        }
        $this->em->flush();
    }

    public function updateAllQuantities(array $quantities): void
    {
        $cart = $this->getCart();
        
        foreach ($quantities as $itemId => $quantity) {
            $item = $this->em->find(CartItem::class, $itemId);
            if ($item && $item->getCart()->getId() === $cart->getId()) {
                if ($quantity > 0) {
                    $item->setQuantity($quantity);
                } else {
                    // remove if zero or negative
                    $cart->removeItem($item);
                }
            }
        }
        
        $this->em->flush();
    }
}

?>