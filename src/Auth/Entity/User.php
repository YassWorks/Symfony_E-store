<?php

namespace App\Auth\Entity;

use App\Auth\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Shared\Enum\Role;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: "`user`")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 100)]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'integer')]
    private int $crystals = 0;

    /**
     * @var Collection<int, \App\Wishlist\Entity\Wishlist>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: \App\Wishlist\Entity\Wishlist::class, cascade: ['remove'])]
    private Collection $wishlists;

    /**
     * @var Collection<int, \App\Review\Entity\Review>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: \App\Review\Entity\Review::class, cascade: ['remove'])]
    private Collection $reviews;

    public function __construct()
    {
        $this->crystals = 0;
        $this->wishlists = new ArrayCollection();
        $this->reviews = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCrystals(): int
    {
        return $this->crystals;
    }

    public function setCrystals(int $crystals): static
    {
        $this->crystals = $crystals;

        return $this;
    }

    public function addCrystals(int $amount): static
    {
        $this->crystals += $amount;

        return $this;
    }

    public function subtractCrystals(int $amount): static
    {
        $this->crystals = max(0, $this->crystals - $amount);

        return $this;
    }

    // role management
    public function getRolesEnums(): array
    {
        return array_map(fn(string $r) => Role::from($r), $this->roles);
    }

    public function addRole(Role $role): static
    {
        if (!in_array($role->value, $this->roles, true)) {
            $this->roles[] = $role->value;
        }

        return $this;
    }

    public function removeRole(Role $role): static
    {
        $this->roles = array_values(array_filter(
            $this->roles,
            fn(string $r) => $r !== $role->value
        ));

        return $this;
    }

    public function hasRole(Role $role): bool
    {
        return in_array($role->value, $this->roles, true);
    }

    // these are required methods from UserInterface
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void {}

    /**
     * @return Collection<int, \App\Wishlist\Entity\Wishlist>
     */
    public function getWishlists(): Collection
    {
        return $this->wishlists;
    }

    public function addWishlist(\App\Wishlist\Entity\Wishlist $wishlist): static
    {
        if (!$this->wishlists->contains($wishlist)) {
            $this->wishlists->add($wishlist);
            $wishlist->setUser($this);
        }

        return $this;
    }    public function removeWishlist(\App\Wishlist\Entity\Wishlist $wishlist): static
    {
        $this->wishlists->removeElement($wishlist);

        return $this;
    }

    /**
     * @return Collection<int, \App\Review\Entity\Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(\App\Review\Entity\Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setUser($this);
        }

        return $this;
    }

    public function removeReview(\App\Review\Entity\Review $review): static
    {
        $this->reviews->removeElement($review);

        return $this;
    }
}
