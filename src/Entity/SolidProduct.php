<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
class SolidProduct extends Product
{
    // Longueur en centimètres (cm)
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    private ?float $lengthCm = null;  // longueur en cm

    // Largeur en centimètres (cm)
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    private ?float $widthCm = null;  // largeur en cm

    // Hauteur en centimètres (cm)
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    private ?float $heightCm = null;  // hauteur en cm

    // Getters et setters avec des noms explicites

    public function getLengthCm(): ?float
    {
        return $this->lengthCm;
    }

    public function setLengthCm(?float $lengthCm): static
    {
        $this->lengthCm = $lengthCm;
        return $this;
    }

    public function getWidthCm(): ?float
    {
        return $this->widthCm;
    }

    public function setWidthCm(?float $widthCm): static
    {
        $this->widthCm = $widthCm;
        return $this;
    }

    public function getHeightCm(): ?float
    {
        return $this->heightCm;
    }

    public function setHeightCm(?float $heightCm): static
    {
        $this->heightCm = $heightCm;
        return $this;
    }
}
