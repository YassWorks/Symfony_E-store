<?php
namespace App\Wishlist\Service;

use App\Wishlist\Entity\Wishlist;
use App\Wishlist\Repository\WishlistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class WishlistService
{
    public function __construct(
        private readonly WishlistRepository $repo,
        private readonly EntityManagerInterface $em,
        private readonly TokenStorageInterface $tokenStorage
    ) {}

    public function getOrCreateByUser(): Wishlist
    {
        $user = $this->tokenStorage->getToken()?->getUser();
        if (!$user) {
            throw new \LogicException('You must be logged in to have a wishlist.');
        }

        $wishlist = $this->repo->findOneBy(['user' => $user]);
        if (!$wishlist) {
            $wishlist = new Wishlist();
            $wishlist->setUser($user);
            $this->em->persist($wishlist);
            $this->em->flush();
        }

        return $wishlist;
    }

    public function addProduct($user, $product): void
    {
        $w = $this->getOrCreateByUser($user);
        $w->addItem($product);
        $this->em->flush();
    }

    public function removeProduct($user, $product): void
    {   
        $w = $this->repo->findOneByUser($user);
        if ($w) {
            $w->removeItem($product);
            $this->em->flush();
        }
    }
}
