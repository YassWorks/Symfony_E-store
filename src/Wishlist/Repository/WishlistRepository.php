<?php
namespace App\Wishlist\Repository;

use App\Wishlist\Entity\Wishlist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class WishlistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wishlist::class);
    }

    /** @return Wishlist[] */
    public function findByUser($user): array
    {
        return $this->findBy(['user' => $user], ['id' => 'DESC']);
    }
}
?>