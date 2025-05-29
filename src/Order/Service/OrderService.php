<?php

namespace App\Order\Service;

use App\Order\Entity\Order;
use App\Order\Entity\OrderItem;
use App\Order\Repository\OrderRepository;
use App\Auth\Entity\User;
use App\Cart\Entity\Cart;
use App\Shop\Entity\Shop;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly OrderRepository $orderRepository
    ) {}

    public function createOrderFromCart(
        User $buyer,
        Cart $cart,
        string $paymentMethod,
        string $deliveryAddress,
        ?int $crystalsUsed = null
    ): Order {
        $order = new Order();
        $order->setBuyer($buyer);
        $order->setTotalAmount((string)$cart->getTotal());
        $order->setPaymentMethod($paymentMethod);
        $order->setDeliveryAddress($deliveryAddress);
        $order->setCrystalsUsed($crystalsUsed);

        // Create order items from cart items
        foreach ($cart->getItems() as $cartItem) {
            $orderItem = new OrderItem();
            $orderItem->setProduct($cartItem->getProduct());
            $orderItem->setQuantity($cartItem->getQuantity());
            $orderItem->setUnitPrice($cartItem->getProduct()->getPrice());
            
            $order->addItem($orderItem);
        }

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $order;
    }

    public function getRecentOrdersForShop(Shop $shop, int $limit = 10): array
    {
        return $this->orderRepository->findRecentOrdersForShop($shop, $limit);
    }

    public function getShopOrderStats(Shop $shop): array
    {
        return $this->orderRepository->getShopOrderStats($shop);
    }
}
