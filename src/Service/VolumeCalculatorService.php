<?php

namespace App\Service;

use App\Entity\Product;

class VolumeCalculatorService
{
    /**
     * Calcule le volume théorique d'un produit basé sur le poids et la densité,
     * ou sur les dimensions si le produit est solide.
     */
    public function calculateVolume(Product $product): float
    {
        if ($product->getDensity() && (float)$product->getDensity() > 0) {
            return (float)$product->getWeight() / (float)$product->getDensity();
        }

        if (
            $product->getProductType() === 'solid' &&
            $product->getDimensionsLength() &&
            $product->getDimensionsWidth() &&
            $product->getDimensionsHeight()
        ) {
            // Convertir les dimensions en mètres
            $length = (float)$product->getDimensionsLength() / 100; // m
            $width = (float)$product->getDimensionsWidth() / 100;   // m
            $height = (float)$product->getDimensionsHeight() / 100; // m

            return $length * $width * $height; // m³
        }

        return 0.0;
    }

    /**
     * Calcule le volume ajusté en fonction de la température pour les produits sensibles.
     */
    public function calculateAdjustedVolume(Product $product, float $volume): float
    {
        if ($product->isTemperatureSensitive() && $product->getTemperature() !== null) {
            if ($product->getProductType() === 'liquid' && $product->getThermalExpansionCoefficient()) {
                return $volume * (1 + $product->getThermalExpansionCoefficient() * ($product->getTemperature() - 20));
            } elseif ($product->getProductType() === 'gas' && $product->getThermalExpansionCoefficientGas()) {
                $kelvin = (float)$product->getTemperature() + 273.15;
                return $volume * ($kelvin / 293.15); // 20°C = 293.15 K
            }
        }

        return $volume;
    }
}
