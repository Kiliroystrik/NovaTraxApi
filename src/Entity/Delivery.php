<?php

namespace App\Entity;

use App\Repository\DeliveryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: DeliveryRepository::class)]
#[ORM\Table(name: 'delivery')]
#[ORM\Index(name: 'idx_delivery_tour_id', columns: ['tour_id'])]
#[ORM\Index(name: 'idx_delivery_company_id', columns: ['company_id'])]
#[ORM\Index(name: 'idx_delivery_status', columns: ['status_id'])]
#[ORM\Index(name: 'idx_delivery_expected_delivery_date', columns: ['expected_delivery_date'])]
#[ORM\Index(name: 'idx_delivery_actual_delivery_date', columns: ['actual_delivery_date'])]

class Delivery
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['clientOrder:read', 'delivery:read', 'tour:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['clientOrder:read', 'delivery:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['clientOrder:read', 'delivery:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    #[Groups(['clientOrder:read', 'delivery:read', 'tour:read'])]
    private ?\DateTimeImmutable $expectedDeliveryDate = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['clientOrder:read', 'delivery:read', 'tour:read'])]
    private ?\DateTimeImmutable $actualDeliveryDate = null;

    #[ORM\ManyToOne(inversedBy: 'deliveries')]
    private ?Tour $tour = null;

    #[ORM\ManyToOne(inversedBy: 'deliveries')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\ManyToOne(inversedBy: 'deliveries')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ClientOrder $clientOrder = null;

    #[ORM\ManyToOne(inversedBy: 'deliveries')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['clientOrder:read', 'delivery:read', 'tour:read'])]
    private ?GeocodedAddress $geocodedAddress = null;

    #[ORM\OneToMany(mappedBy: 'delivery', targetEntity: DeliveryProduct::class, cascade: ['persist', 'remove'])]
    #[Groups(['clientOrder:read', 'delivery:read', 'tour:read'])]
    private Collection $productDeliveries;

    #[ORM\ManyToOne(inversedBy: 'deliveries')]
    #[Groups(['clientOrder:read', 'delivery:read', 'tour:read'])]
    private ?Status $status = null;

    public function __construct()
    {
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

    public function getExpectedDeliveryDate(): ?\DateTimeImmutable
    {
        return $this->expectedDeliveryDate;
    }

    public function setExpectedDeliveryDate(\DateTimeImmutable $expectedDeliveryDate): static
    {
        $this->expectedDeliveryDate = $expectedDeliveryDate;

        return $this;
    }

    public function getActualDeliveryDate(): ?\DateTimeImmutable
    {
        return $this->actualDeliveryDate;
    }

    public function setActualDeliveryDate(?\DateTimeImmutable $actualDeliveryDate): static
    {
        $this->actualDeliveryDate = $actualDeliveryDate;

        return $this;
    }

    public function getTour(): ?Tour
    {
        return $this->tour;
    }

    public function setTour(?Tour $tour): static
    {
        $this->tour = $tour;

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

    public function getClientOrder(): ?ClientOrder
    {
        return $this->clientOrder;
    }

    public function setClientOrder(?ClientOrder $clientOrder): static
    {
        $this->clientOrder = $clientOrder;

        return $this;
    }

    public function getGeocodedAddress(): ?GeocodedAddress
    {
        return $this->geocodedAddress;
    }

    public function setGeocodedAddress(?GeocodedAddress $geocodedAddress): static
    {
        $this->geocodedAddress = $geocodedAddress;

        return $this;
    }

    /**
     * @return Collection<int, DeliveryProduct>
     */
    public function getProductDeliveries(): Collection
    {
        return $this->productDeliveries;
    }

    public function addProductDelivery(DeliveryProduct $productDelivery): self
    {
        if (!$this->productDeliveries->contains($productDelivery)) {
            $this->productDeliveries->add($productDelivery);
            $productDelivery->setDelivery($this);
        }

        return $this;
    }

    public function removeProductDelivery(DeliveryProduct $productDelivery): self
    {
        if ($this->productDeliveries->removeElement($productDelivery)) {
            // set the owning side to null (unless already changed)
            if ($productDelivery->getDelivery() === $this) {
                $productDelivery->setDelivery(null);
            }
        }

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): static
    {
        $this->status = $status;

        return $this;
    }
}
