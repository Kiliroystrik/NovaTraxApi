<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\Vehicle;
use App\Service\PasswordHashService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

class VehicleFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private PasswordHashService $passwordHashService) {}

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create('fr_FR');

        $breaker = 0;

        for ($i = 0; $i < 10; $i++) {
            $company = $this->getReference('company-' . $i, Company::class);

            for ($j = 0; $j < 30; $j++) {
                $vehicle = new Vehicle();
                $vehicle->setCompany($company);
                $vehicle->setLicensePlate($faker->ean8());

                // Définir le poids et le volume
                $weight = $faker->randomFloat(2, 1000, 20000); // Poids entre 1,000 kg et 20,000 kg
                $volume = $faker->randomFloat(2, 5, 100);     // Volume entre 5 m³ et 100 m³

                $vehicle->setWeight(number_format($weight, 2, '.', '')); // Stocker comme string
                $vehicle->setVolume(number_format($volume, 2, '.', ''));

                $manager->persist($vehicle);

                $this->setReference('vehicle-' . $breaker, $vehicle);

                $breaker++;
            }
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CompanyFixtures::class
        ];
    }
}
