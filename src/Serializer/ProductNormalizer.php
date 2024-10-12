<?php
// src/Serializer/ProductNormalizer.php

namespace App\Serializer;

use App\Entity\Product;
use App\Service\VolumeCalculatorService;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ProductNormalizer implements NormalizerInterface
{
    private ObjectNormalizer $objectNormalizer;
    private VolumeCalculatorService $volumeCalculator;

    public function __construct(ObjectNormalizer $objectNormalizer, VolumeCalculatorService $volumeCalculator)
    {
        $this->objectNormalizer = $objectNormalizer;
        $this->volumeCalculator = $volumeCalculator;
    }

    /**
     * Normalise l'objet en ajoutant les champs calculés.
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        /** @var Product $object */
        // Normalise l'objet avec l'ObjectNormalizer par défaut
        $data = $this->objectNormalizer->normalize($object, $format, $context);

        // Calcule le volume
        $calculatedVolume = $this->volumeCalculator->calculateVolume($object);
        $adjustedVolume = $this->volumeCalculator->calculateAdjustedVolume($object, $calculatedVolume);

        // Ajoute les champs calculés
        $data['volume'] = $calculatedVolume;
        $data['theoreticalVolume'] = $calculatedVolume; // Vous pouvez ajuster selon votre logique
        $data['adjustedVolume'] = $adjustedVolume;

        return $data;
    }

    /**
     * Indique si ce normalizer supporte la normalisation de l'objet.
     */
    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Product;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Product::class => true,
        ];
    }
}
