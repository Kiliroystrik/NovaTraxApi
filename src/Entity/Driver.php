<?php

namespace App\Entity;

use App\Repository\DriverRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: DriverRepository::class)]
#[ORM\Table(name: "driver")]
#[ORM\Index(name: "idx_driver_company", columns: ["company_id"])]
#[ORM\Index(name: "idx_driver_license_number", columns: ["license_number"])]
#[ORM\Index(name: "idx_driver_first_name", columns: ["first_name"])]
#[ORM\Index(name: "idx_driver_last_name", columns: ["last_name"])]
class Driver
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['driver:read', 'driver:list', 'tour:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['driver:read', 'driver:list'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['driver:read', 'driver:list'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 50)]
    #[Groups(['driver:read', 'driver:list', 'tour:read'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 50)]
    #[Groups(['driver:read', 'driver:list', 'tour:read'])]
    private ?string $lastName = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['driver:read', 'driver:list', 'tour:read'])]
    private ?string $licenseNumber = null;

    #[ORM\ManyToOne(inversedBy: 'drivers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['driver:read', 'driver:list'])]
    private ?Company $company = null;

    /**
     * @var Collection<int, Tour>
     */
    #[ORM\OneToMany(targetEntity: Tour::class, mappedBy: 'driver')]
    private Collection $tours;

    /**
     * @var Collection<int, Unavailability>
     */
    #[ORM\OneToMany(targetEntity: Unavailability::class, mappedBy: 'driver')]
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

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getLicenseNumber(): ?string
    {
        return $this->licenseNumber;
    }

    public function setLicenseNumber(?string $licenseNumber): static
    {
        $this->licenseNumber = $licenseNumber;

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
            $tour->setDriver($this);
        }

        return $this;
    }

    public function removeTour(Tour $tour): static
    {
        if ($this->tours->removeElement($tour)) {
            // set the owning side to null (unless already changed)
            if ($tour->getDriver() === $this) {
                $tour->setDriver(null);
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
            $unavailability->setDriver($this);
        }

        return $this;
    }

    public function removeUnavailability(Unavailability $unavailability): static
    {
        if ($this->unavailabilities->removeElement($unavailability)) {
            // set the owning side to null (unless already changed)
            if ($unavailability->getDriver() === $this) {
                $unavailability->setDriver(null);
            }
        }

        return $this;
    }
}
