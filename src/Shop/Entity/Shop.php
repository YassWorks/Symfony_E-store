<?php

namespace App\Shop\Entity;

use App\Shop\Repository\ShopRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Shared\Enum\Category;

#[ORM\Entity(repositoryClass: ShopRepository::class)]
#[ORM\Table(name: "`shop`")]
class Shop
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column(type: 'json')]
    private array $categories = [];

    #[ORM\Column(length: 255)]
    private ?string $website = null;

    #[ORM\Column(length: 100)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $logo_url = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

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

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function setCategories(array $categories): static
    {
        $this->categories = $categories;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(string $website): static
    {
        $this->website = $website;

        return $this;
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

    public function getLogoUrl(): ?string
    {
        return $this->logo_url;
    }

    public function setLogoUrl(string $logo_url): static
    {
        $this->logo_url = $logo_url;

        return $this;
    }

    // category management
    public function addCategory(string $category): static
    {
        if (!in_array($category, $this->categories, true)) {
            $this->categories[] = $category;
        }

        return $this;
    }
    public function removeCategory(string $category): static
    {
        $this->categories = array_values(array_filter(
            $this->categories,
            fn(string $c) => $c !== $category
        ));

        return $this;
    }

    public function hasCategory(string $category): bool
    {
        return in_array($category, $this->categories, true);
    }

    public function getCategoriesEnum(): array
    {
        return array_map(fn(string $r) => Category::from($r), $this->categories);
    }
}
