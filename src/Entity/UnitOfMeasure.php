<?php

namespace App\Entity;

use App\Repository\UnitOfMeasureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: UnitOfMeasureRepository::class)]
class UnitOfMeasure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['clientOrder:read', 'delivery:read', 'unitOfMeasure:read', 'unitOfMeasure:list'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['clientOrder:read', 'delivery:read', 'unitOfMeasure:read', 'unitOfMeasure:list'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['clientOrder:read', 'delivery:read', 'unitOfMeasure:read', 'unitOfMeasure:list'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Groups(['clientOrder:read', 'delivery:read', 'unitOfMeasure:read', 'unitOfMeasure:list'])]
    private ?string $name = null;

    #[ORM\Column(length: 10)]
    #[Groups(['clientOrder:read', 'delivery:read', 'unitOfMeasure:read', 'unitOfMeasure:list'])]
    private ?string $symbol = null;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'unitOfMeasure', orphanRemoval: true)]
    private Collection $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

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

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setUnitOfMeasure($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getUnitOfMeasure() === $this) {
                $product->setUnitOfMeasure(null);
            }
        }

        return $this;
    }

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(string $symbol): static
    {
        $this->symbol = $symbol;

        return $this;
    }
}
