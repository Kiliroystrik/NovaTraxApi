<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
class LiquidProduct extends Product
{
    // Densité en kg/L (kilogrammes par litre)
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 3, nullable: true)]
    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    private ?float $densityKgPerLiter = null;  // densité par litre

    // Indique si le produit est sensible à la température
    #[ORM\Column(type: Types::BOOLEAN)]
    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    private bool $isTemperatureSensitive = false;

    // Coefficient de dilatation thermique (expansion volumétrique en fonction de la température)
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 6, nullable: true)]
    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    private ?float $thermalExpansionCoefficientPerDegreeCelsius = null;  // coefficient de dilatation thermique par °C


    // Getters et setters avec des noms explicites

    public function getDensityKgPerLiter(): ?float
    {
        return $this->densityKgPerLiter;
    }

    public function setDensityKgPerLiter(?float $densityKgPerLiter): self
    {
        $this->densityKgPerLiter = $densityKgPerLiter;

        return $this;
    }

    public function isTemperatureSensitive(): bool
    {
        return $this->isTemperatureSensitive;
    }

    public function setIsTemperatureSensitive(bool $isTemperatureSensitive): self
    {
        $this->isTemperatureSensitive = $isTemperatureSensitive;

        return $this;
    }

    public function getThermalExpansionCoefficientPerDegreeCelsius(): ?float
    {
        return $this->thermalExpansionCoefficientPerDegreeCelsius;
    }

    public function setThermalExpansionCoefficientPerDegreeCelsius(?float $thermalExpansionCoefficientPerDegreeCelsius): self
    {
        $this->thermalExpansionCoefficientPerDegreeCelsius = $thermalExpansionCoefficientPerDegreeCelsius;

        return $this;
    }
}
