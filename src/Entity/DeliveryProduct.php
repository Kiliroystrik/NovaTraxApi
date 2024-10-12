<?php

namespace App\Entity;

use App\Repository\DeliveryProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: DeliveryProductRepository::class)]
class DeliveryProduct
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['clientOrder:read', 'delivery:read', 'tour:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'productDeliveries')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['clientOrder:read', 'delivery:read', 'tour:read'])]
    private ?Product $product = null;

    #[ORM\ManyToOne(inversedBy: 'productDeliveries')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Delivery $delivery = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 3)]
    #[Groups(['clientOrder:read', 'delivery:read', 'tour:read'])]
    private ?string $quantity = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getDelivery(): ?Delivery
    {
        return $this->delivery;
    }

    public function setDelivery(?Delivery $delivery): static
    {
        $this->delivery = $delivery;

        return $this;
    }

    public function getQuantity(): ?string
    {
        return $this->quantity;
    }

    public function setQuantity(string $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }
}
