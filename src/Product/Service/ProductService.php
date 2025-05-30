<?php
namespace App\Product\Service;

use App\Product\Entity\Product;
use App\Product\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProductService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ProductRepository $repo
    ) {}

    public function list(): iterable
    {
        return $this->repo->findAll();
    }

    public function get(int $id): ?Product
    {
        return $this->repo->find($id);
    }

    public function save(Product $product): void
    {
        $this->em->persist($product);
        $this->em->flush();
    }    
    
    public function delete(Product $product): void
    {
        // Remove related OrderItems first to avoid foreign key constraint violation
        $orderItems = $this->em->getRepository(\App\Order\Entity\OrderItem::class)
            ->findBy(['product' => $product]);
        
        foreach ($orderItems as $orderItem) {
            $this->em->remove($orderItem);
        }
        
        // Remove related CartItems to avoid foreign key constraint violation
        $cartItems = $this->em->getRepository(\App\Cart\Entity\CartItem::class)
            ->findBy(['product' => $product]);
        
        foreach ($cartItems as $cartItem) {
            $this->em->remove($cartItem);
        }
        
        // Remove from wishlists (many-to-many relationship)
        $wishlists = $this->em->getRepository(\App\Wishlist\Entity\Wishlist::class)->findAll();
        foreach ($wishlists as $wishlist) {
            $wishlist->removeItem($product);
        }
        
        $this->em->remove($product);
        $this->em->flush();
    }
    /**
     *
     * @param array{q:string, minPrice:?float, maxPrice:?float, shop:?int, categories:string[]} $criteria
     * @return Product[]
     */
    public function findByFilters(array $criteria): array
    {
        return $this->repo->findByFilters($criteria);
    }
}