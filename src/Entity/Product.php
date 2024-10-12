<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 100)]
    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    private ?string $description = null;

    /**
     * @var Collection<int, DeliveryProduct>
     */
    #[ORM\OneToMany(targetEntity: DeliveryProduct::class, mappedBy: 'product', orphanRemoval: true)]
    private Collection $productDeliveries;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['product:read', 'product:list'])]
    private ?Company $company = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    private ?UnitOfMeasure $unitOfMeasure = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 3)]
    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    private ?string $weight = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $density = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $dimensionsLength = null; // en cm

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $dimensionsWidth = null; // en cm

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $dimensionsHeight = null; // en cm

    #[ORM\Column(type: Types::BOOLEAN, options: ["default" => false])]
    private bool $isTemperatureSensitive = false;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $temperature = null;

    #[ORM\Column(length: 10)]
    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    private ?string $productType = null; // 'solid', 'liquid', 'gas'

    // Ajout du champ virtuel 'volume'
    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    private ?float $volume = null;

    // Ajout des coefficients d'expansion
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 6, nullable: true)]
    private ?string $thermalExpansionCoefficient = null; // Pour les liquides

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 6, nullable: true)]
    private ?string $thermalExpansionCoefficientGas = null; // Pour les gaz

    public function __construct()
    {
        $this->productDeliveries = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, DeliveryProduct>
     */
    public function getProductDeliveries(): Collection
    {
        return $this->productDeliveries;
    }

    public function addDeliveryProduct(DeliveryProduct $deliveryProduct): static
    {
        if (!$this->productDeliveries->contains($deliveryProduct)) {
            $this->productDeliveries->add($deliveryProduct);
            $deliveryProduct->setProduct($this);
        }

        return $this;
    }

    public function removeDeliveryProduct(DeliveryProduct $deliveryProduct): static
    {
        if ($this->productDeliveries->removeElement($deliveryProduct)) {
            // set the owning side to null (unless already changed)
            if ($deliveryProduct->getProduct() === $this) {
                $deliveryProduct->setProduct(null);
            }
        }

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getUnitOfMeasure(): ?UnitOfMeasure
    {
        return $this->unitOfMeasure;
    }

    public function setUnitOfMeasure(?UnitOfMeasure $unitOfMeasure): static
    {
        $this->unitOfMeasure = $unitOfMeasure;

        return $this;
    }

    public function getWeight(): ?string
    {
        return $this->weight;
    }

    public function setWeight(string $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    // Getters et Setters pour les nouveaux champs
    public function getDensity(): ?float
    {
        return $this->density !== null ? (float)$this->density : null;
    }

    public function setDensity(?float $density): static
    {
        $this->density = $density !== null ? (string)$density : null;

        return $this;
    }

    public function getDimensionsLength(): ?float
    {
        return $this->dimensionsLength !== null ? (float)$this->dimensionsLength : null;
    }

    public function setDimensionsLength(?float $dimensionsLength): static
    {
        $this->dimensionsLength = $dimensionsLength !== null ? (string)$dimensionsLength : null;

        return $this;
    }

    public function getDimensionsWidth(): ?float
    {
        return $this->dimensionsWidth !== null ? (float)$this->dimensionsWidth : null;
    }

    public function setDimensionsWidth(?float $dimensionsWidth): static
    {
        $this->dimensionsWidth = $dimensionsWidth !== null ? (string)$dimensionsWidth : null;

        return $this;
    }

    public function getDimensionsHeight(): ?float
    {
        return $this->dimensionsHeight !== null ? (float)$this->dimensionsHeight : null;
    }

    public function setDimensionsHeight(?float $dimensionsHeight): static
    {
        $this->dimensionsHeight = $dimensionsHeight !== null ? (string)$dimensionsHeight : null;

        return $this;
    }

    public function isTemperatureSensitive(): bool
    {
        return $this->isTemperatureSensitive;
    }

    public function setIsTemperatureSensitive(bool $isTemperatureSensitive): static
    {
        $this->isTemperatureSensitive = $isTemperatureSensitive;

        return $this;
    }

    public function getTemperature(): ?float
    {
        return $this->temperature !== null ? (float)$this->temperature : null;
    }

    public function setTemperature(?float $temperature): static
    {
        $this->temperature = $temperature !== null ? (string)$temperature : null;

        return $this;
    }

    public function getProductType(): ?string
    {
        return $this->productType;
    }

    public function setProductType(string $productType): static
    {
        $this->productType = $productType;

        return $this;
    }

    public function getThermalExpansionCoefficient(): ?float
    {
        return $this->thermalExpansionCoefficient !== null ? (float)$this->thermalExpansionCoefficient : null;
    }

    public function setThermalExpansionCoefficient(?float $thermalExpansionCoefficient): static
    {
        $this->thermalExpansionCoefficient = $thermalExpansionCoefficient !== null ? (string)$thermalExpansionCoefficient : null;

        return $this;
    }

    public function getThermalExpansionCoefficientGas(): ?float
    {
        return $this->thermalExpansionCoefficientGas !== null ? (float)$this->thermalExpansionCoefficientGas : null;
    }

    public function setThermalExpansionCoefficientGas(?float $thermalExpansionCoefficientGas): static
    {
        $this->thermalExpansionCoefficientGas = $thermalExpansionCoefficientGas !== null ? (string)$thermalExpansionCoefficientGas : null;

        return $this;
    }

    public function getVolume(): ?float
    {
        return $this->volume !== null ? (float)$this->volume : null;
    }

    public function setVolume(?float $volume): static
    {
        $this->volume = $volume !== null ? (string)$volume : null;

        return $this;
    }
}
