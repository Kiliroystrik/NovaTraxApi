<?php

namespace App\Entity;

use App\Repository\VehicleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: VehicleRepository::class)]
#[ORM\Index(name: 'idx_vehicle_company', columns: ['company_id'])]
#[ORM\Index(name: 'idx_vehicle_license_plate', columns: ['license_plate'])]
class Vehicle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['vehicle:read', 'vehicle:list'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['vehicle:read', 'vehicle:list'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['vehicle:read', 'vehicle:list'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['vehicle:read', 'vehicle:list'])]
    private ?string $licensePlate = null;

    #[ORM\Column(length: 50)]
    #[Groups(['vehicle:read', 'vehicle:list'])]
    private ?string $type = null;

    #[ORM\Column(length: 50)]
    #[Groups(['vehicle:read', 'vehicle:list'])]
    private ?string $model = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['vehicle:read', 'vehicle:list'])]
    private ?string $capacity = null;

    #[ORM\ManyToOne(inversedBy: 'vehicles')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['vehicle:read', 'vehicle:list'])]
    private ?Company $company = null;

    /**
     * @var Collection<int, Tour>
     */
    #[ORM\OneToMany(targetEntity: Tour::class, mappedBy: 'vehicle')]
    private Collection $tours;

    /**
     * @var Collection<int, Unavailability>
     */
    #[ORM\OneToMany(targetEntity: Unavailability::class, mappedBy: 'vehicle')]
    private Collection $unavailabilities;

    public function __construct()
    {
        $this->tours = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->unavailabilities = new ArrayCollection();
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

    public function getLicensePlate(): ?string
    {
        return $this->licensePlate;
    }

    public function setLicensePlate(?string $licensePlate): static
    {
        $this->licensePlate = $licensePlate;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getCapacity(): ?string
    {
        return $this->capacity;
    }

    public function setCapacity(string $capacity): static
    {
        $this->capacity = $capacity;

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
     * @return Collection<int, Tour>
     */
    public function getTours(): Collection
    {
        return $this->tours;
    }

    public function addTour(Tour $tour): static
    {
        if (!$this->tours->contains($tour)) {
            $this->tours->add($tour);
            $tour->setVehicle($this);
        }

        return $this;
    }

    public function removeTour(Tour $tour): static
    {
        if ($this->tours->removeElement($tour)) {
            // set the owning side to null (unless already changed)
            if ($tour->getVehicle() === $this) {
                $tour->setVehicle(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Unavailability>
     */
    public function getUnavailabilities(): Collection
    {
        return $this->unavailabilities;
    }

    public function addUnavailability(Unavailability $unavailability): static
    {
        if (!$this->unavailabilities->contains($unavailability)) {
            $this->unavailabilities->add($unavailability);
            $unavailability->setVehicle($this);
        }

        return $this;
    }

    public function removeUnavailability(Unavailability $unavailability): static
    {
        if ($this->unavailabilities->removeElement($unavailability)) {
            // set the owning side to null (unless already changed)
            if ($unavailability->getVehicle() === $this) {
                $unavailability->setVehicle(null);
            }
        }

        return $this;
    }
}
