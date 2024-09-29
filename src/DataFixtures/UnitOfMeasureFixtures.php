<?php

namespace App\DataFixtures;

use App\Entity\UnitOfMeasure;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UnitOfMeasureFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Liste des noms d'unités de mesure pertinentes pour le transport/logistique
        $measures = [
            'Kilogram',  // Poids
            'Tonne',
            'Pound',
            'Ounce',
            'Liter',    // Volume
            'Cubic Meter',
            'Gallon',
            'Meter',    // Longueur
            'Centimeter',
            'Foot',
            'Inch',
            'Unit'      // Unité générale
        ];

        // Liste des symboles correspondants
        $symbols = [
            'kg',   // Kilogramme
            't',    // Tonne
            'lb',   // Livre
            'oz',   // Once
            'L',    // Litre
            'm³',   // Mètre cube
            'gal',  // Gallon
            'm',    // Mètre
            'cm',   // Centimètre
            'ft',   // Pied
            'in',   // Pouce
            'u'     // Unité générique
        ];

        // Boucle pour créer chaque unité de mesure avec son symbole
        for ($i = 0; $i < count($measures); $i++) {
            $unitOfMeasure = new UnitOfMeasure();
            $unitOfMeasure->setName($measures[$i]);
            $unitOfMeasure->setSymbol($symbols[$i]); // Définir le symbole correspondant

            // Persister l'unité de mesure dans le gestionnaire
            $manager->persist($unitOfMeasure);

            // Créer une référence pour utiliser cette entité dans d'autres fixtures si nécessaire
            $this->addReference('unit-of-measure-' . $i, $unitOfMeasure);
        }

        // Sauvegarder tous les changements dans la base de données
        $manager->flush();
    }
}
