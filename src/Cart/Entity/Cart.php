<?php
namespace App\Cart\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Cart\Entity\CartItem;
use App\Auth\Entity\User;
use App\Cart\Repository\CartRepository;

#[ORM\Entity(repositoryClass: CartRepository::class)]
#[ORM\Table(name: "`cart`")]
class Cart
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\OneToMany(mappedBy: 'cart', targetEntity: CartItem::class, cascade: ['persist','remove'], orphanRemoval: true)]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getUser(): User  { return $this->user; }
    public function setUser(User $u): self { $this->user = $u; return $this; }
    public function getItems(): Collection { return $this->items; }

    public function addItem(CartItem $i): self
    {
        if (!$this->items->contains($i)) {
            $this->items->add($i);
            $i->setCart($this);
        }
        return $this;
    }
    
    public function removeItem(CartItem $i): self
    {
        if ($this->items->contains($i)) {
            $this->items->removeElement($i);
            // Set the owning side to null (important for orphan removal)
            $i->setCart(null);
        }
        return $this;
    }

    public function getTotal(): float
    {
        return array_sum(
            $this->items
                 ->map(fn(CartItem $i) => $i->getQuantity() * (float)$i->getProduct()->getPrice())
                 ->toArray()
        );
    }
}


?>