<?php

namespace App\Cart\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Product\Entity\Product;
use App\Cart\Repository\CartItemRepository;

#[ORM\Entity(repositoryClass: CartItemRepository::class)]
#[ORM\Table(name: "`cart_item`")]
class CartItem
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Cart::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private Cart $cart;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Product $product;

    #[ORM\Column(type: 'integer')]
    private int $quantity = 1;

    public function getId(): ?int { return $this->id; }
    public function getCart(): Cart { return $this->cart; }
    public function setCart(Cart $c): self { $this->cart = $c; return $this; }
    public function getProduct(): Product { return $this->product; }
    public function setProduct(Product $p): self { $this->product = $p; return $this; }
    public function getQuantity(): int { return $this->quantity; }
    public function setQuantity(int $q): self { $this->quantity = $q; return $this; }
}


?>