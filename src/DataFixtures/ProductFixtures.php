<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\Product;
use App\Entity\UnitOfMeasure;
use App\Service\PasswordHashService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private PasswordHashService $passwordHashService) {}

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create('fr_FR');

        // Pour chaque Company (10), je crée 10 produits
        for ($i = 0; $i < 10; $i++) {
            $randomCompany = $this->getReference('company-' . $i, Company::class); // Récupérer chaque entreprise par défaut

            $product = new Product();
            $product->setName($faker->word());
            $product->setCompany($randomCompany);
            $product->setDescription($faker->realText(200));
            // J'utilise faker pour generer des nombres aleatoires entre 0 et 8 qui correspond au nombre d'unites de mesure disponibles
            $unitOfMeasure = $faker->numberBetween(0, 8);
            $product->setUnitOfMeasure($this->getReference('unit-of-measure-' . $unitOfMeasure, UnitOfMeasure::class));
            $manager->persist($product);

            $this->addReference('product-' . $i, $product);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CompanyFixtures::class,
            UnitOfMeasureFixtures::class
        ];
    }
}
