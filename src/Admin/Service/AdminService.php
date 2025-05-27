<?php

namespace App\Admin\Service;

use App\Auth\Entity\User;
use App\Shop\Entity\Shop;
use App\Shared\Enum\Role;
use App\Cart\Entity\Cart;
use App\Product\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

class AdminService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private string $adminPasskey
    ) {}

    public function validatePasskey(string $passkey): bool
    {
        return $passkey === $this->adminPasskey;
    }

    public function promoteToAdmin(User $user): void
    {
        if (!$user->hasRole(Role::ROLE_ADMIN)) {
            $user->addRole(Role::ROLE_ADMIN);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }

    public function deleteUser(User $user): void
    {
        // Cascade delete all shops owned by the user
        $shops = $this->entityManager->getRepository(Shop::class)->findBy(['owner' => $user]);
        foreach ($shops as $shop) {
            $this->deleteShop($shop);
        }

        // Cascade delete all carts associated with the user
        $carts = $this->entityManager->getRepository(Cart::class)->findBy(['user' => $user]);
        foreach ($carts as $cart) {
            $this->entityManager->remove($cart);
        }

        // Finally, delete the user
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }
    
    public function deleteShop(Shop $shop): void
    {
        // Cascade delete all products associated with the shop
        $products = $this->entityManager->getRepository(Product::class)->findBy(['shop' => $shop]);
        foreach ($products as $product) {
            $this->entityManager->remove($product);
        }

        // Remove the seller role from the shop owner
        $owner = $shop->getOwner();
        if ($owner && $owner->hasRole(Role::ROLE_SELLER)) {
            $owner->removeRole(Role::ROLE_SELLER);
            $this->entityManager->persist($owner);
        }

        $this->entityManager->remove($shop);
        $this->entityManager->flush();
    }
}
