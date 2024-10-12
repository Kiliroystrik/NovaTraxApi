<?php

namespace App\Entity;

use App\Repository\WarehouseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: WarehouseRepository::class)]
class Warehouse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['tour:read', 'tour:list', 'warehouse:read', 'warehouse:list'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['tour:read', 'tour:list', 'warehouse:read', 'warehouse:list'])]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'warehouses')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['tour:read', 'tour:list', 'warehouse:read', 'warehouse:list'])]
    private ?GeocodedAddress $address = null;

    /**
     * @var Collection<int, Tour>
     */
    #[ORM\OneToMany(targetEntity: Tour::class, mappedBy: 'loading')]
    private Collection $tours;

    #[ORM\ManyToOne(inversedBy: 'warehouses')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['tour:read', 'tour:list'])]
    private ?Company $company = null;

    public function __construct()
    {
        $this->tours = new ArrayCollection();
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

    public function getAddress(): ?GeocodedAddress
    {
        return $this->address;
    }

    public function setAddress(?GeocodedAddress $address): static
    {
        $this->address = $address;

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
            $tour->setLoading($this);
        }

        return $this;
    }

    public function removeTour(Tour $tour): static
    {
        if ($this->tours->removeElement($tour)) {
            // set the owning side to null (unless already changed)
            if ($tour->getLoading() === $this) {
                $tour->setLoading(null);
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
}
