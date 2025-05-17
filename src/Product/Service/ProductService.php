<?php
namespace App\Product\Service;

use App\Product\Entity\Product;
use App\Product\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProductService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ProductRepository $repo
    ) {}

    public function list(): iterable
    {
        return $this->repo->findAll();
    }

    public function get(int $id): ?Product
    {
        return $this->repo->find($id);
    }

    public function save(Product $product): void
    {
        $this->em->persist($product);
        $this->em->flush();
    }

    public function delete(Product $product): void
    {
        $this->em->remove($product);
        $this->em->flush();
    }
}