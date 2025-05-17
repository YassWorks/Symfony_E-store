<?php
namespace App\Product\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Image
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string')]
    private string $url;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $altText = null;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: false)]
    private Product $product;

    // getters & setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function getAltText(): ?string
    {
        return $this->altText;
    }

    public function setAltText(?string $altText): self
    {
        $this->altText = $altText;
        return $this;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): self
    {
        $this->product = $product;
        return $this;
    }
}