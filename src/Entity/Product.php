<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap(['liquid' => LiquidProduct::class, 'solid' => SolidProduct::class])]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    private ?string $description = null;

    // virtual calculatedVolume
    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    private ?float $calculatedVolume = null;

    /**
     * @var Collection<int, DeliveryProduct>
     */
    #[ORM\OneToMany(targetEntity: DeliveryProduct::class, mappedBy: 'product', orphanRemoval: true)]
    private Collection $productDeliveries;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['product:read', 'product:list'])]
    private ?Company $company = null;

    // Poids en kilogrammes, avec un type DECIMAL pour éviter les erreurs d'arrondi
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 3, nullable: true)]
    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    private ?float $weightKg = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    private bool $isHazardous = false;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    private ?string $hazardClass = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ["default" => false])]
    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    private bool $adrCompliant = false;

    public function __construct()
    {
        $this->productDeliveries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getWeightKg(): ?float
    {
        return $this->weightKg;
    }

    public function setWeightKg(?float $weightKg): static
    {
        $this->weightKg = $weightKg;
        return $this;
    }

    public function isHazardous(): ?bool
    {
        return $this->isHazardous;
    }

    public function setIsHazardous(bool $isHazardous): static
    {
        $this->isHazardous = $isHazardous;
        return $this;
    }

    public function getHazardClass(): ?string
    {
        return $this->hazardClass;
    }

    public function setHazardClass(?string $hazardClass): static
    {
        $this->hazardClass = $hazardClass;
        return $this;
    }

    public function isAdrCompliant(): bool
    {
        return $this->adrCompliant;
    }

    public function setAdrCompliant(bool $adrCompliant): static
    {
        $this->adrCompliant = $adrCompliant;
        return $this;
    }

    public function getCalculatedVolume(): ?float
    {
        return $this->calculatedVolume;
    }

    public function setCalculatedVolume(?float $calculatedVolume): static
    {
        $this->calculatedVolume = $calculatedVolume;
        return $this;
    }

    // Renvoyer le type de produit
    // Implémentation futur de nouveaux types de produits
    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    public function getType(): string
    {
        if ($this instanceof LiquidProduct) {
            return 'liquid';
        } elseif ($this instanceof SolidProduct) {
            return 'solid';
        }

        return 'unknown';
    }
}
