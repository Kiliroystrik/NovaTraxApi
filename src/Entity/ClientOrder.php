<?php

namespace App\Entity;

use App\Repository\ClientOrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ClientOrderRepository::class)]
class ClientOrder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['clientOrder:list', 'clientOrder:read', 'delivery:read', 'delivery:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 20, unique: true)]
    #[Groups(['clientOrder:list', 'clientOrder:read', 'delivery:read', 'delivery:write'])]
    private ?string $orderNumber = null;

    #[ORM\Column]
    #[Groups(['clientOrder:list', 'clientOrder:read', 'delivery:read', 'delivery:write'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['clientOrder:list', 'clientOrder:read', 'delivery:read', 'delivery:write'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    #[Groups(['clientOrder:list', 'clientOrder:read', 'delivery:read', 'delivery:write'])]
    private ?\DateTimeImmutable $clientOrderDate = null;

    #[ORM\Column]
    #[Groups(['clientOrder:list', 'clientOrder:read', 'delivery:read', 'delivery:write'])]
    private ?\DateTimeImmutable $expectedDeliveryDate = null;

    #[ORM\Column(length: 50)]
    #[Groups(['clientOrder:list', 'clientOrder:read', 'delivery:read', 'delivery:write'])]
    private ?string $status = null;

    /**
     * @var Collection<int, Delivery>
     */
    #[ORM\OneToMany(targetEntity: Delivery::class, mappedBy: 'clientOrder', orphanRemoval: true)]
    #[Groups(['clientOrder:read', 'delivery:read', 'delivery:write'])]
    private Collection $deliveries;

    #[ORM\ManyToOne(inversedBy: 'clientOrders')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['clientOrder:read', 'delivery:read', 'delivery:write'])]
    private ?Company $company = null;

    #[ORM\ManyToOne(inversedBy: 'clientOrders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    public function __construct()
    {
        $this->deliveries = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderNumber(): ?string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(string $orderNumber): static
    {
        $this->orderNumber = $orderNumber;

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

    public function getClientOrderDate(): ?\DateTimeImmutable
    {
        return $this->clientOrderDate;
    }

    public function setClientOrderDate(\DateTimeImmutable $clientOrderDate): static
    {
        $this->clientOrderDate = $clientOrderDate;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

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
            $delivery->setClientOrder($this);
        }

        return $this;
    }

    public function removeDelivery(Delivery $delivery): static
    {
        if ($this->deliveries->removeElement($delivery)) {
            // set the owning side to null (unless already changed)
            if ($delivery->getClientOrder() === $this) {
                $delivery->setClientOrder(null);
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

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }
}
