<?php

namespace App\Entity;

use App\Repository\TourRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: TourRepository::class)]
#[ORM\Index(name: 'idx_tour_status', columns: ['status_id'])]
#[ORM\Index(name: 'idx_tour_driver_id', columns: ['driver_id'])]
#[ORM\Index(name: 'idx_tour_company_id', columns: ['company_id'])]
#[ORM\Index(name: 'idx_tour_vehicle_id', columns: ['vehicle_id'])]
#[ORM\Index(name: 'idx_tour_start_date', columns: ['start_date'])]
#[ORM\Index(name: 'idx_tour_end_date', columns: ['end_date'])]

class Tour
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['tour:read', 'tour:list'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['tour:read', 'tour:list'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['tour:read', 'tour:list'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['tour:read', 'tour:list'])]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['tour:read', 'tour:list'])]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\ManyToOne(inversedBy: 'tours')]
    #[Groups(['tour:read'])]
    private ?Driver $driver = null;

    #[ORM\ManyToOne(inversedBy: 'tours')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['tour:read', 'tour:list'])]
    private ?Company $company = null;

    #[ORM\ManyToOne(inversedBy: 'tours')]
    #[Groups(['tour:read'])]
    private ?Vehicle $vehicle = null;

    /**
     * @var Collection<int, Delivery>
     */
    #[ORM\OneToMany(targetEntity: Delivery::class, mappedBy: 'tour')]
    #[Groups(['tour:read'])]
    private Collection $deliveries;

    #[ORM\Column(length: 20, unique: true)]
    #[Groups(['tour:read', 'tour:list'])]
    private ?string $tourNumber = null;

    #[ORM\ManyToOne(inversedBy: 'tours')]
    #[Groups(['tour:read', 'tour:list'])]
    private ?Warehouse $loading = null;

    #[ORM\ManyToOne(inversedBy: 'tours')]
    #[Groups(['tour:read', 'tour:list'])]
    private ?Status $status = null;

    public function __construct()
    {
        $this->deliveries = new ArrayCollection();
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

    public function getstartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setstartDate(?\DateTimeImmutable $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeImmutable $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getDriver(): ?Driver
    {
        return $this->driver;
    }

    public function setDriver(?Driver $driver): static
    {
        $this->driver = $driver;

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

    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    public function setVehicle(?Vehicle $vehicle): static
    {
        $this->vehicle = $vehicle;

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
            $delivery->setTour($this);
        }

        return $this;
    }

    public function removeDelivery(Delivery $delivery): static
    {
        if ($this->deliveries->removeElement($delivery)) {
            // set the owning side to null (unless already changed)
            if ($delivery->getTour() === $this) {
                $delivery->setTour(null);
            }
        }

        return $this;
    }

    public function getTourNumber(): ?string
    {
        return $this->tourNumber;
    }

    public function setTourNumber(string $tourNumber): static
    {
        $this->tourNumber = $tourNumber;

        return $this;
    }

    public function getLoading(): ?Warehouse
    {
        return $this->loading;
    }

    public function setLoading(?Warehouse $loading): static
    {
        $this->loading = $loading;

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
