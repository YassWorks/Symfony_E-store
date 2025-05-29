<?php

namespace App\Review\Repository;

use App\Review\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    /**
     *
     * @return Review[]
     */
    public function findByProduct(int $productId): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.product = :pid')
            ->setParameter('pid', $productId)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
    public function getAverageRatingForProduct(int $productId): ?float
    {
        return $this->createQueryBuilder('r')
            ->select('AVG(r.rating) as avgRating')
            ->where('r.product = :productId')
            ->setParameter('productId', $productId)
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function getAverageRatingsForShopProducts(array $productIds): array
    {
        $qb = $this->createQueryBuilder('r')
            ->select('IDENTITY(r.product) AS productId, AVG(r.rating) AS avgRating')
            ->where('r.product IN (:productIds)')
            ->groupBy('r.product')
            ->setParameter('productIds', $productIds);

        $result = $qb->getQuery()->getResult();

        $averages = [];
        foreach ($result as $row) {
            $averages[$row['productId']] = round($row['avgRating'], 1);
        }

        return $averages;
    }
}
