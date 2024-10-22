<?php

namespace App\Entity;

use App\Repository\StatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: StatusRepository::class)]
#[ORM\Table(name: "status")]
#[ORM\UniqueConstraint(name: "status_unique", columns: ["name", "type"])]
class Status
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['clientOrder:read', 'clientOrder:list', 'delivery:read', 'delivery:list', 'tour:read', 'tour:list', 'status:read', 'status:list'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['clientOrder:read', 'clientOrder:list', 'delivery:read', 'delivery:list', 'tour:read', 'tour:list', 'status:read', 'status:list'])]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    #[Groups(['clientOrder:read', 'clientOrder:list', 'delivery:read', 'delivery:list', 'tour:read', 'tour:list', 'status:read', 'status:list'])]
    private ?string $type = null;

    /**
     * @var Collection<int, Tour>
     */
    #[ORM\OneToMany(targetEntity: Tour::class, mappedBy: 'status')]
    private Collection $tours;

    /**
     * @var Collection<int, ClientOrder>
     */
    #[ORM\OneToMany(targetEntity: ClientOrder::class, mappedBy: 'status')]
    private Collection $clientOrders;

    /**
     * @var Collection<int, Delivery>
     */
    #[ORM\OneToMany(targetEntity: Delivery::class, mappedBy: 'status')]
    private Collection $deliveries;

    public function __construct()
    {
        $this->tours = new ArrayCollection();
        $this->clientOrders = new ArrayCollection();
        $this->deliveries = new ArrayCollection();
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

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
            $tour->setStatus($this);
        }

        return $this;
    }

    public function removeTour(Tour $tour): static
    {
        if ($this->tours->removeElement($tour)) {
            // set the owning side to null (unless already changed)
            if ($tour->getStatus() === $this) {
                $tour->setStatus(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ClientOrder>
     */
    public function getClientOrders(): Collection
    {
        return $this->clientOrders;
    }

    public function addClientOrder(ClientOrder $clientOrder): static
    {
        if (!$this->clientOrders->contains($clientOrder)) {
            $this->clientOrders->add($clientOrder);
            $clientOrder->setStatus($this);
        }

        return $this;
    }

    public function removeClientOrder(ClientOrder $clientOrder): static
    {
        if ($this->clientOrders->removeElement($clientOrder)) {
            // set the owning side to null (unless already changed)
            if ($clientOrder->getStatus() === $this) {
                $clientOrder->setStatus(null);
            }
        }

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
            $delivery->setStatus($this);
        }

        return $this;
    }

    public function removeDelivery(Delivery $delivery): static
    {
        if ($this->deliveries->removeElement($delivery)) {
            // set the owning side to null (unless already changed)
            if ($delivery->getStatus() === $this) {
                $delivery->setStatus(null);
            }
        }

        return $this;
    }
}
