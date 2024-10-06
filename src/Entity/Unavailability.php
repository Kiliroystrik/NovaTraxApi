<?php

namespace App\Entity;

use App\Repository\UnavailabilityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UnavailabilityRepository::class)]
#[ORM\Table(name: "unavailability")]
#[ORM\Index(name: "idx_driver", columns: ["driver_id"])]
#[ORM\Index(name: "idx_vehicle", columns: ["vehicle_id"])]
#[ORM\Index(name: "idx_reason", columns: ["reason_id"])]
#[ORM\Index(name: "idx_start_date", columns: ["start_date"])]
#[ORM\Index(name: "idx_end_date", columns: ["end_date"])]

class Unavailability
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'unavailabilities')]
    private ?Driver $driver = null;

    #[ORM\ManyToOne(inversedBy: 'unavailabilities')]
    private ?Vehicle $vehicle = null;

    #[ORM\ManyToOne(inversedBy: 'unavailabilities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Reason $reason = null;

    #[ORM\ManyToOne(inversedBy: 'unavailabilities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): static
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

    public function getDriver(): ?Driver
    {
        return $this->driver;
    }

    public function setDriver(?Driver $driver): static
    {
        $this->driver = $driver;

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

    public function getReason(): ?Reason
    {
        return $this->reason;
    }

    public function setReason(?Reason $reason): static
    {
        $this->reason = $reason;

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
}
