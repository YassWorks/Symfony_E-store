<?php

namespace App\Cart\Service;

use App\Cart\Entity\Cart;
use App\Cart\Entity\CartItem;
use App\Cart\Repository\CartRepository;
use App\Product\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use function Symfony\Component\VarDumper\dump;

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

        // checking if it's the first item in the cart. If so we'll need to handling it differently
        if ($cart->getItems()->count() === 1 && $cart->getItems()->first()->getId() === $itemId) {
            // if it's the only item, we can just clear the cart
            $cart->getItems()->clear();
            $this->em->remove($item);
            $this->em->flush();
            return;
        }

        if ($item && $item->getCart() && $item->getCart()->getId() === $cart->getId()) {
            $cart->removeItem($item);
            $this->em->remove($item);
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

            // check stock
            $product = $item->getProduct();
            if ($quantity > $product->getStockQuantity()) {
                throw new \InvalidArgumentException(
                    sprintf('Cannot set quantity to %d. Only %d items available in stock.', 
                    $quantity, $product->getStockQuantity())
                );
            }

            $item->setQuantity($quantity);

        } elseif ($item) {
            $cart = $item->getCart();
            $cart->removeItem($item);
            $this->em->remove($item);
        }
        $this->em->flush();
    }      public function updateAllQuantities(array $quantities): void
    {
        $cart = $this->getCart();
          foreach ($quantities as $itemId => $quantity) {
            $item = $this->em->find(CartItem::class, $itemId);
            if ($item && $item->getCart() && $item->getCart()->getId() === $cart->getId()) {
                if ($quantity > 0) {

                    $product = $item->getProduct();
                    if ($quantity > $product->getStockQuantity()) {
                        throw new \InvalidArgumentException(
                            sprintf('Cannot set quantity to %d for "%s". Only %d items available in stock.', 
                            $quantity, $product->getTitle(), $product->getStockQuantity())
                        );
                    }
                    $item->setQuantity($quantity);
                } else {

                    $cart->removeItem($item);
                    $this->em->remove($item);
                }
            }
        }
        
        $this->em->flush();
    }
    
    public function getProductQuantityInCart(int $productId): int
    {
        $cart = $this->getCart();
        $existing = $cart->getItems()
            ->filter(fn($i) => $i->getProduct()->getId() === $productId)
            ->first();
        
        return $existing ? $existing->getQuantity() : 0;
    }
}