<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\LiquidProduct;
use App\Entity\SolidProduct;

class VolumeCalculatorService
{
    /**
     * Je calcule le volume théorique d'un produit.
     * 
     * Si le produit est un **liquide**, je calcule le volume en fonction du poids (en kilogrammes) et de la densité
     * (exprimée en kg/L). Le volume résultant est en litres.
     * 
     * Si le produit est **solide**, je calcule le volume en fonction de ses dimensions (longueur, largeur, hauteur),
     * exprimées en centimètres. Je convertis ces dimensions en mètres et calcule le volume en mètres cubes (m³).
     * 
     * @param Product $product L'entité produit pour laquelle je dois calculer le volume.
     * 
     * @return float Le volume calculé en fonction du type de produit.
     */
    public function calculateVolume(Product $product): float
    {
        // Si le produit est un liquide, je calcule le volume en fonction du poids et de la densité.
        if ($product instanceof LiquidProduct) {
            if ($product->getDensityKgPerLiter() && (float)$product->getDensityKgPerLiter() > 0) {
                return (float)$product->getWeightKg() / (float)$product->getDensityKgPerLiter();  // volume en litres
            }
        }

        // Si le produit est solide, je calcule le volume en fonction des dimensions.
        if ($product instanceof SolidProduct) {
            if (
                $product->getLengthCm() &&
                $product->getWidthCm() &&
                $product->getHeightCm()
            ) {
                // Convertir les dimensions en mètres
                $length = (float)$product->getLengthCm() / 100;  // en m
                $width = (float)$product->getWidthCm() / 100;    // en m
                $height = (float)$product->getHeightCm() / 100;  // en m

                // Je retourne le volume en mètres cubes (m³)
                return $length * $width * $height;
            }
        }

        // Si aucune des conditions n'est remplie, je retourne un volume de 0
        return 0.0;
    }

    /**
     * Je calcule le volume ajusté en fonction de la température pour les produits sensibles à la température.
     * 
     * Si le produit est un **liquide** sensible à la température, je calcule le volume ajusté en utilisant
     * le coefficient de dilatation thermique du produit et la différence entre la température actuelle
     * et une température de référence de 20°C.
     * 
     * Je ne traite pour l'instant que les liquides, mais cette méthode peut être étendue pour d'autres types de produits.
     * 
     * @param Product $product Le produit dont je dois ajuster le volume.
     * @param float $volume Le volume actuel du produit.
     * @param float|null $temperature La température à laquelle je dois ajuster le volume (en °C).
     * 
     * @return float Le volume ajusté en fonction de la température, ou le volume d'origine si aucun ajustement n'est nécessaire.
     */
    public function calculateAdjustedVolume(Product $product, float $volume, ?float $temperature): float
    {
        // Je vérifie d'abord si le produit est un liquide et sensible à la température
        if ($product instanceof LiquidProduct && $product->isTemperatureSensitive() && $temperature !== null) {
            // Si le produit a un coefficient de dilatation thermique, j'ajuste le volume
            if ($product->getThermalExpansionCoefficientPerDegreeCelsius()) {
                return $volume * (1 + $product->getThermalExpansionCoefficientPerDegreeCelsius() * ($temperature - 20));
            }
        }

        // Si aucune condition n'est remplie, je retourne le volume d'origine
        return $volume;
    }
}
