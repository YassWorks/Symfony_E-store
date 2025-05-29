<?php
namespace App\Product\Repository;

use App\Product\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findByFilters(array $c): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.shop', 's')
            ->addSelect('s');

        // name search
        if (!empty($c['q'])) {
            $qb->andWhere('p.title LIKE :q')
            ->setParameter('q', '%'.$c['q'].'%');
        }

        // price range
        if ($c['minPrice'] !== null) {
            $qb->andWhere('p.price >= :minPrice')
            ->setParameter('minPrice', $c['minPrice']);
        }
        if ($c['maxPrice'] !== null) {
            $qb->andWhere('p.price <= :maxPrice')
            ->setParameter('maxPrice', $c['maxPrice']);
        }

        // filter by shop
        if (!empty($c['shop'])) {
            $qb->andWhere('s.id = :shopId')
            ->setParameter('shopId', $c['shop']);
        }

        // categories (enum values)
        if (!empty($c['categories'])) {
            $qb->andWhere('p.category IN (:cats)')
            ->setParameter('cats', $c['categories']);
        }

        return $qb
            ->orderBy('p.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

}