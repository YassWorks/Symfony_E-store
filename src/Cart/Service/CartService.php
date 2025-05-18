<?php
namespace App\Cart\Service;

use App\Cart\Entity\Cart;
use App\Cart\Entity\CartItem;
use App\Cart\Repository\CartRepository;
use App\Product\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
        $user = $this->tokenStore->getToken()->getUser();
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
}

?>