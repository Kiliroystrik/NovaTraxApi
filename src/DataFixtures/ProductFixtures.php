<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\Product;
use App\Entity\UnitOfMeasure;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Liste des carburants avec leurs noms exacts
        $fuelProducts = [
            'Gazole',
            'GPL (Gaz de Pétrole Liquéfié)',
            'GNL (Gaz Naturel Liquéfié)',
            'Kérosène',
            'Biodiesel',
            'Éthanol',
            'Propane',
            'Super Sans Plomb',
            'Diesel FAP',
            'Super 95'
        ];

        // Liste des autres produits
        $otherProducts = [
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

        // Fusion des deux listes pour créer une liste complète de produits
        $allProducts = array_merge($fuelProducts, $otherProducts);

        // Récupérer toutes les unités de mesure disponibles (0 à 11)
        $unitOfMeasures = [];
        for ($u = 0; $u < 12; $u++) {
            $unitOfMeasures[] = $this->getReference('unit-of-measure-' . $u, UnitOfMeasure::class);
        }

        // Parcourir chaque entreprise pour créer des produits
        for ($i = 0; $i < 10; $i++) {
            /** @var Company $company */
            $company = $this->getReference('company-' . $i, Company::class);

            foreach ($allProducts as $productName) {
                $product = new Product();
                $product->setName($productName);
                $product->setCompany($company);
                $product->setDescription($this->generateDescription($productName));
                $product->setWeight('0.5');
                $product->setVolume('0.5');

                // Assigner une unité de mesure pertinente en fonction du produit
                $unitOfMeasure = $this->assignUnitOfMeasure($productName, $unitOfMeasures);
                $product->setUnitOfMeasure($unitOfMeasure);

                $manager->persist($product);

                // Créer une référence unique pour chaque produit
                // Format : 'product-{companyIndex}-{productName}'
                $referenceName = 'product-' . $i . '-' . strtolower(str_replace([' ', '(', ')', '-'], '-', $productName));
                $this->addReference($referenceName, $product);
            }
        }

        $manager->flush();
    }

    /**
     * Génère une description basée sur le nom du produit.
     *
     * @param string $productName
     * @return string
     */
    private function generateDescription(string $productName): string
    {
        $descriptions = [
            'Gazole' => 'Carburant utilisé principalement pour les moteurs diesel.',
            'GPL (Gaz de Pétrole Liquéfié)' => 'Carburant alternatif pour véhicules et équipements.',
            'GNL (Gaz Naturel Liquéfié)' => 'Gaz naturel transformé en liquide pour faciliter le transport.',
            'Kérosène' => 'Carburant utilisé principalement dans l’aviation.',
            'Biodiesel' => 'Carburant renouvelable produit à partir de matières organiques.',
            'Éthanol' => 'Alcool utilisé comme carburant renouvelable ou dans les boissons.',
            'Propane' => 'Gaz utilisé pour le chauffage, la cuisson et comme carburant alternatif.',
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

    /**
     * Assigne une unité de mesure pertinente en fonction du nom du produit.
     *
     * @param string $productName
     * @param UnitOfMeasure[] $unitOfMeasures
     * @return UnitOfMeasure
     */
    private function assignUnitOfMeasure(string $productName, array $unitOfMeasures): UnitOfMeasure
    {
        // Définir des règles simples pour assigner des unités de mesure
        $fuelProducts = [
            'Gazole',
            'GPL (Gaz de Pétrole Liquéfié)',
            'GNL (Gaz Naturel Liquéfié)',
            'Kérosène',
            'Biodiesel',
            'Éthanol',
            'Propane',
            'Super Sans Plomb',
            'Diesel FAP',
            'Super 95'
        ];

        $objectProducts = [
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

        if (in_array($productName, $fuelProducts)) {
            // Pour les carburants, assigner 'Liter' (unit-of-measure-4) ou 'Gallon' (unit-of-measure-6)
            $preferredUnits = ['Liter', 'Gallon'];
        } elseif (in_array($productName, $objectProducts)) {
            // Pour les objets, assigner 'Unit' (unit-of-measure-11)
            $preferredUnits = ['Unit'];
        } else {
            // Unité par défaut
            $preferredUnits = ['Unit'];
        }

        foreach ($preferredUnits as $unitName) {
            foreach ($unitOfMeasures as $unit) {
                if (strtolower($unit->getName()) === strtolower($unitName)) {
                    return $unit;
                }
            }
        }

        // Si aucune unité préférée n'est trouvée, retourner 'Unit' par défaut
        foreach ($unitOfMeasures as $unit) {
            if (strtolower($unit->getName()) === 'unit') {
                return $unit;
            }
        }

        // Si 'Unit' n'est pas trouvé, retourner la première unité disponible
        return $unitOfMeasures[0];
    }

    public function getDependencies()
    {
        return [
            CompanyFixtures::class,
            UnitOfMeasureFixtures::class
        ];
    }
}
