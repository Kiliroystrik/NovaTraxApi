<?php

namespace App\Entity;

use App\Repository\GeocodedAddressRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: GeocodedAddressRepository::class)]
class GeocodedAddress
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['clientOrder:read', 'delivery:read', 'tour:read', 'warehouse:read', 'warehouse:list'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['clientOrder:read', 'delivery:read', 'warehouse:read', 'warehouse:list'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['clientOrder:read', 'delivery:read', 'warehouse:read', 'warehouse:list'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255)]
    #[Groups(['clientOrder:read', 'delivery:read', 'tour:read', 'warehouse:read', 'warehouse:list'])]
    private ?string $streetName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['clientOrder:read', 'delivery:read', 'tour:read', 'warehouse:read', 'warehouse:list'])]
    private ?string $fullAddress = null;

    #[ORM\Column(length: 255)]
    #[Groups(['clientOrder:read', 'delivery:read', 'tour:read', 'warehouse:read', 'warehouse:list'])]
    private ?string $city = null;

    #[ORM\Column(length: 20)]
    #[Groups(['clientOrder:read', 'delivery:read', 'tour:read', 'warehouse:read', 'warehouse:list'])]
    private ?string $postalCode = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['clientOrder:read', 'delivery:read', 'tour:read', 'warehouse:read', 'warehouse:list'])]
    private ?string $department = null;

    #[ORM\Column(length: 100)]
    #[Groups(['clientOrder:read', 'delivery:read', 'tour:read', 'warehouse:read', 'warehouse:list'])]
    private ?string $country = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 8, nullable: true)]
    #[Groups(['clientOrder:read', 'delivery:read', 'tour:read', 'warehouse:read', 'warehouse:list'])]
    private ?string $latitude = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 11, scale: 8, nullable: true)]
    #[Groups(['clientOrder:read', 'delivery:read', 'tour:read', 'warehouse:read', 'warehouse:list'])]
    private ?string $longitude = null;

    #[ORM\Column(length: 10)]
    #[Groups(['clientOrder:read', 'delivery:read', 'tour:read', 'warehouse:read', 'warehouse:list'])]
    private ?string $streetNumber = null;

    #[ORM\Column]
    #[Groups(['clientOrder:read', 'delivery:read', 'tour:read', 'warehouse:read', 'warehouse:list'])]
    private ?bool $isVerified = null;

    #[ORM\Column(length: 20)]
    #[Groups(['clientOrder:read', 'delivery:read', 'tour:read', 'warehouse:read', 'warehouse:list'])]
    private ?string $source = null;

    /**
     * @var Collection<int, Delivery>
     */
    #[ORM\OneToMany(targetEntity: Delivery::class, mappedBy: 'geocodedAddress', orphanRemoval: true)]
    private Collection $deliveries;

    #[ORM\ManyToOne(inversedBy: 'GeocodedAddresses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    /**
     * @var Collection<int, Warehouse>
     */
    #[ORM\OneToMany(targetEntity: Warehouse::class, mappedBy: 'address', orphanRemoval: true)]
    private Collection $warehouses;

    public function __construct()
    {
        $this->deliveries = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->isVerified = false;
        $this->warehouses = new ArrayCollection();
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


    public function getStreetName(): ?string
    {
        return $this->streetName;
    }

    public function setStreetName(string $streetName): static
    {
        $this->streetName = $streetName;

        return $this;
    }

    public function getFullAddress(): ?string
    {
        return $this->fullAddress;
    }

    public function setFullAddress(?string $fullAddress): static
    {
        $this->fullAddress = $fullAddress;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function setDepartment(?string $department): static
    {
        $this->department = $department;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getStreetNumber(): ?string
    {
        return $this->streetNumber;
    }

    public function setStreetNumber(string $streetNumber): static
    {
        $this->streetNumber = $streetNumber;

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(string $source): static
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return Collection<int, Delivery>
     */
    public function getDeliveries(): Collection
    {
        return $this->deliveries;
    }

    public function addDelivery(Delivery $delivery): static
    {
        if (!$this->deliveries->contains($delivery)) {
            $this->deliveries->add($delivery);
            $delivery->setGeocodedAddress($this);
        }

        return $this;
    }

    public function removeDelivery(Delivery $delivery): static
    {
        if ($this->deliveries->removeElement($delivery)) {
            // set the owning side to null (unless already changed)
            if ($delivery->getGeocodedAddress() === $this) {
                $delivery->setGeocodedAddress(null);
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

    /**
     * @return Collection<int, Warehouse>
     */
    public function getWarehouses(): Collection
    {
        return $this->warehouses;
    }

    public function addWarehouse(Warehouse $warehouse): static
    {
        if (!$this->warehouses->contains($warehouse)) {
            $this->warehouses->add($warehouse);
            $warehouse->setAddress($this);
        }

        return $this;
    }

    public function removeWarehouse(Warehouse $warehouse): static
    {
        if ($this->warehouses->removeElement($warehouse)) {
            // set the owning side to null (unless already changed)
            if ($warehouse->getAddress() === $this) {
                $warehouse->setAddress(null);
            }
        }

        return $this;
    }
}
