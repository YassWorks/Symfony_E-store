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
        $newEmail = $form->get('email')->getData();
        if ($newEmail !== $user->getEmail()) {
            $existingUser = $this->userRepository->findOneBy(['email' => $newEmail]);
            if ($existingUser && $existingUser !== $user) {
                return false; // email already taken
            }
        }

        $user->setName($form->get('name')->getData());
        $user->setEmail($newEmail);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return true;
    }

    public function deleteUserAccount(User $user): void
    {
        // 1. handle user's shops (and their products)
        $shops = $this->entityManager->getRepository(Shop::class)->findBy(['owner' => $user]);
        foreach ($shops as $shop) {
            $this->deleteShop($shop);
        }

        // 2. handle user's carts
        $carts = $this->entityManager->getRepository(Cart::class)->findBy(['user' => $user]);
        foreach ($carts as $cart) {
            $this->entityManager->remove($cart);
        }

        // 3. wishlists are already handled by cascade: ['remove'] in User entity

        // 4. finally, remove the user
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    private function deleteShop(Shop $shop): void
    {
        // remove products first
        $products = $this->entityManager->getRepository(Product::class)->findBy(['shop' => $shop]);
        foreach ($products as $product) {
            $this->entityManager->remove($product);
        }

        // remove seller role from the owner
        $owner = $shop->getOwner();
        if ($owner && $owner->hasRole(Role::ROLE_SELLER)) {
            $otherShops = $this->entityManager->getRepository(Shop::class)->createQueryBuilder('s') // they could have other shops
                ->where('s.owner = :owner')
                ->andWhere('s.id != :currentShopId')
                ->setParameter('owner', $owner)
                ->setParameter('currentShopId', $shop->getId())
                ->getQuery()
                ->getResult();

            if (empty($otherShops)) {
                $owner->removeRole(Role::ROLE_SELLER);
                $this->entityManager->persist($owner);
            }
        }

        $this->entityManager->remove($shop);
        $this->entityManager->flush();
    }
}