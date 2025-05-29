<?php
namespace App\Wishlist\Entity;

use App\Auth\Entity\User;
use App\Product\Entity\Product;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Wishlist\Repository\WishlistRepository::class)]
#[ORM\Table(name: "`wishlist`")]
class Wishlist
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'wishlists')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\ManyToMany(targetEntity: Product::class)]
    #[ORM\JoinTable(name: 'wishlist_products')]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    /** @return Collection<int, Product> */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(Product $product): static
    {
        if (!$this->items->contains($product)) {
            $this->items->add($product);
        }
        return $this;
    }

    public function removeItem(Product $product): static
    {
        $this->items->removeElement($product);
        return $this;
    }
}