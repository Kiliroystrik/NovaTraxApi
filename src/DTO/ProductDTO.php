<?php

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class ProductDto
{
    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    public int $id;

    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list'])]
    public \DateTimeImmutable $createdAt;

    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list'])]
    public ?\DateTimeImmutable $updatedAt;

    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    public string $name;

    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    public ?string $description;

    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    public string $weight;

    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    public ?float $density;

    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    public ?float $dimensionsLength;

    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    public ?float $dimensionsWidth;

    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    public ?float $dimensionsHeight;

    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    public string $productType; // 'solid', 'liquid', 'gas'

    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    public float $volume; // Champ calculé

    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    public float $theoreticalVolume; // Champ calculé

    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    public float $adjustedVolume; // Champ calculé

    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    public ?float $temperature;

    #[Groups(['clientOrder:read', 'delivery:read', 'product:read', 'product:list', 'tour:read'])]
    public bool $isTemperatureSensitive;
}
