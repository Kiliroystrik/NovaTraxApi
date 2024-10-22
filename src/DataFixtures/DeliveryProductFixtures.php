<?php

namespace App\DataFixtures;

use App\Entity\Delivery;
use App\Entity\DeliveryProduct;
use App\Entity\Product;
use App\Entity\LiquidProduct;
use App\Entity\SolidProduct;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class DeliveryProductFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 3000; $i++) {
            /** @var Delivery $delivery */
            $delivery = $this->getReference('delivery-' . $i);
            $deliveryCompany = $delivery->getCompany();

            // Récupérer les produits associés à la compagnie
            $productRepository = $manager->getRepository(Product::class);
            $products = $productRepository->findBy(['company' => $deliveryCompany]);

            if (empty($products)) {
                continue; // Pas de produits disponibles pour cette compagnie
            }

            /** @var Product $product */
            $product = $products[array_rand($products)];

            $deliveryProduct = new DeliveryProduct();
            $deliveryProduct->setDelivery($delivery);
            $deliveryProduct->setProduct($product);

            // Si product est un liquide, initialiser la quantité avec un float
            if ($product instanceof LiquidProduct) {
                $deliveryProduct->setQuantity((string)$this->getRandomFloat(1, 100));
            } else if ($product instanceof SolidProduct) {
                // Si le produit est un solide, initialiser la quantité avec un entier converti en float
                $deliveryProduct->setQuantity((string)round($this->getRandomFloat(1, 100)));
            }

            // Vérification du type de produit pour gérer la température
            $temperature = null;
            if ($product instanceof LiquidProduct) {
                // Si le produit est liquide, générer une température pertinente
                if ($product->isTemperatureSensitive()) {
                    $temperature = $this->getRandomFloat(15, 35);
                }
            }

            // Assigner la température si pertinente
            $deliveryProduct->setTemperature($temperature);

            $manager->persist($deliveryProduct);
        }

        // Flush final
        $manager->flush();
    }

    /**
     * Génère un nombre flottant aléatoire entre deux valeurs.
     *
     * @param float $min
     * @param float $max
     * @return float
     */
    private function getRandomFloat(float $min, float $max): float
    {
        return round($min + mt_rand() / mt_getrandmax() * ($max - $min), 2);
    }

    public function getDependencies()
    {
        return [
            DeliveryFixtures::class,
            ProductFixtures::class,
            StatusFixtures::class,
        ];
    }
}
