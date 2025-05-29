<?php

namespace App\Core\Service;

use App\Auth\Entity\User;
use App\Auth\Repository\UserRepository;
use App\Shop\Entity\Shop;
use App\Cart\Entity\Cart;
use App\Product\Entity\Product;
use App\Shared\Enum\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;

class ProfileService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository
    ) {}

    public function updateProfile(User $user, FormInterface $form): bool
    {
        // Check if email has changed and if it's already taken by another user
        $newEmail = $form->get('email')->getData();
        if ($newEmail !== $user->getEmail()) {
            $existingUser = $this->userRepository->findOneBy(['email' => $newEmail]);
            if ($existingUser && $existingUser !== $user) {
                return false; // Email already taken
            }
        }

        // Update user data
        $user->setName($form->get('name')->getData());
        $user->setEmail($newEmail);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return true;
    }

    public function deleteUserAccount(User $user): void
    {
        // Handle all related entities with proper cascading

        // 1. Handle user's shops (and their products)
        $shops = $this->entityManager->getRepository(Shop::class)->findBy(['owner' => $user]);
        foreach ($shops as $shop) {
            $this->deleteShop($shop);
        }

        // 2. Handle user's carts
        $carts = $this->entityManager->getRepository(Cart::class)->findBy(['user' => $user]);
        foreach ($carts as $cart) {
            $this->entityManager->remove($cart);
        }

        // 3. Wishlists are already handled by cascade: ['remove'] in User entity

        // 4. Finally, remove the user
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    private function deleteShop(Shop $shop): void
    {
        // Remove products first (they have the shop reference)
        $products = $this->entityManager->getRepository(Product::class)->findBy(['shop' => $shop]);
        foreach ($products as $product) {
            $this->entityManager->remove($product);
        }

        // Remove seller role from the owner if they have no other shops
        $owner = $shop->getOwner();
        if ($owner && $owner->hasRole(Role::ROLE_SELLER)) {
            $otherShops = $this->entityManager->getRepository(Shop::class)->createQueryBuilder('s')
                ->where('s.owner = :owner')
                ->andWhere('s.id != :currentShopId')
                ->setParameter('owner', $owner)
                ->setParameter('currentShopId', $shop->getId())
                ->getQuery()
                ->getResult();

            // Only remove seller role if this is their last shop
            if (empty($otherShops)) {
                $owner->removeRole(Role::ROLE_SELLER);
                $this->entityManager->persist($owner);
            }
        }

        $this->entityManager->remove($shop);
        $this->entityManager->flush();
    }
}