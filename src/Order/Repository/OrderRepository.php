<?php

namespace App\Order\Repository;

use App\Order\Entity\Order;
use App\Shop\Entity\Shop;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * Find recent orders for products belonging to a specific shop
     * 
     * @param Shop $shop
     * @param int $limit
     * @return Order[]
     */
    public function findRecentOrdersForShop(Shop $shop, int $limit = 10): array
    {
        return $this->createQueryBuilder('o')
            ->innerJoin('o.items', 'oi')
            ->innerJoin('oi.product', 'p')
            ->innerJoin('p.shop', 's')
            ->where('s.id = :shopId')
            ->setParameter('shopId', $shop->getId())
            ->orderBy('o.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get order statistics for a shop
     * 
     * @param Shop $shop
     * @return array
     */
    public function getShopOrderStats(Shop $shop): array
    {
        $result = $this->createQueryBuilder('o')
            ->select('COUNT(DISTINCT o.id) as totalOrders')
            ->addSelect('SUM(oi.quantity * oi.unitPrice) as totalRevenue')
            ->addSelect('SUM(oi.quantity) as totalItemsSold')
            ->innerJoin('o.items', 'oi')
            ->innerJoin('oi.product', 'p')
            ->innerJoin('p.shop', 's')
            ->where('s.id = :shopId')
            ->setParameter('shopId', $shop->getId())
            ->getQuery()
            ->getSingleResult();

        return [
            'totalOrders' => (int)($result['totalOrders'] ?? 0),
            'totalRevenue' => (float)($result['totalRevenue'] ?? 0),
            'totalItemsSold' => (int)($result['totalItemsSold'] ?? 0)
        ];
    }
}
