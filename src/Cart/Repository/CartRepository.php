<?php
// src/Cart/Repository/CartRepository.php
namespace App\Cart\Repository;

use App\Cart\Entity\Cart;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cart>
 */
class CartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cart::class);
    }

    public function findOneByUser(int $userId): ?Cart
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.user = :uid')
            ->setParameter('uid', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
