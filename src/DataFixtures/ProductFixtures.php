<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\LiquidProduct;
use App\Entity\SolidProduct;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Liste des carburants liquides (exclusion des gaz)
        $liquidProducts = [
            'Gazole' => 0.85,
            'Kérosène' => 0.82,
            'Biodiesel' => 0.88,
            'Éthanol' => 0.79,
            'Super Sans Plomb' => 0.75,
            'Diesel FAP' => 0.83,
            'Super 95' => 0.74
        ];

        // Liste des autres produits solides
        $solidProducts = [
            'Ordinateur Portable',
            'Téléphone Portable',
            'Chaise de Bureau',
            'Table de Réunion',
            'Imprimante',
            'Papeterie',
            'Nettoyant Multi-Usage',
            'Boissons',
            'Snacks',
            'Équipements de Sécurité'
        ];

        // Parcourir chaque entreprise pour créer des produits
        for ($i = 0; $i < 10; $i++) {
            /** @var Company $company */
            $company = $this->getReference('company-' . $i);

            // Créer des produits liquides
            foreach ($liquidProducts as $productName => $density) {
                $product = new LiquidProduct();
                $product->setName($productName);
                $product->setCompany($company);
                $product->setDescription($this->generateDescription($productName));
                $product->setDensityKgPerLiter($density); // Densité en kg/L
                $product->setWeightKg($density); // Poids correspondant à 1 litre du produit
                $product->setIsTemperatureSensitive(true);
                $product->setThermalExpansionCoefficientPerDegreeCelsius($this->getRandomFloat(0.0003, 0.0007));

                $manager->persist($product);

                // Créer une référence unique pour chaque produit
                $referenceName = 'product-' . $i . '-' . strtolower(str_replace([' ', '(', ')', '-'], '-', $productName));
                $this->addReference($referenceName, $product);
            }

            // Créer des produits solides
            foreach ($solidProducts as $productName) {
                $product = new SolidProduct();
                $product->setName($productName);
                $product->setCompany($company);
                $product->setDescription($this->generateDescription($productName));
                $product->setWeightKg($this->getRandomFloat(1, 100)); // Poids en kg

                // Définir les dimensions en cm pour les produits solides
                $product->setLengthCm($this->getRandomFloat(20, 200)); // Longueur en cm
                $product->setWidthCm($this->getRandomFloat(20, 200));  // Largeur en cm
                $product->setHeightCm($this->getRandomFloat(10, 100)); // Hauteur en cm

                $manager->persist($product);

                // Créer une référence unique pour chaque produit
                $referenceName = 'product-' . $i . '-' . strtolower(str_replace([' ', '(', ')', '-'], '-', $productName));
                $this->addReference($referenceName, $product);
            }
        }

        $manager->flush();
    }

    private function generateDescription(string $productName): string
    {
        $descriptions = [
            'Gazole' => 'Carburant utilisé principalement pour les moteurs diesel.',
            'Kérosène' => 'Carburant utilisé principalement dans l’aviation.',
            'Biodiesel' => 'Carburant renouvelable produit à partir de matières organiques.',
            'Éthanol' => 'Alcool utilisé comme carburant renouvelable ou dans les boissons.',
            'Super Sans Plomb' => 'Essence de haute qualité sans plomb pour moteurs à essence.',
            'Diesel FAP' => 'Diesel avec filtre à particules pour réduire les émissions.',
            'Super 95' => 'Essence à indice d’octane élevé pour moteurs performants.',
            'Ordinateur Portable' => 'Appareil électronique portable pour les tâches informatiques.',
            'Téléphone Portable' => 'Appareil de communication mobile.',
            'Chaise de Bureau' => 'Mobilier ergonomique pour le confort au travail.',
            'Table de Réunion' => 'Mobilier utilisé pour les réunions et les collaborations.',
            'Imprimante' => 'Dispositif pour imprimer des documents numériques.',
            'Papeterie' => 'Fournitures de bureau comme les stylos, papiers, etc.',
            'Nettoyant Multi-Usage' => 'Produit de nettoyage polyvalent pour diverses surfaces.',
            'Boissons' => 'Produits liquides consommables comme les jus, sodas, etc.',
            'Snacks' => 'Aliments légers et rapides à consommer.',
            'Équipements de Sécurité' => 'Matériel utilisé pour assurer la sécurité sur le lieu de travail.'
        ];

        return $descriptions[$productName] ?? 'Description du produit.';
    }

    private function getRandomFloat(float $min, float $max): float
    {
        return round($min + mt_rand() / mt_getrandmax() * ($max - $min), 4);
    }

    public function getDependencies()
    {
        return [
            CompanyFixtures::class,
        ];
    }
}
