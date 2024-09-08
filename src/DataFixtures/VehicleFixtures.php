<?php

namespace App\DataFixtures;

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
            $company = $this->getReference('company-' . $i);

            for ($j = 0; $j < 30; $j++) {
                $vehicle = new Vehicle();
                $vehicle->setCompany($company);
                $vehicle->setLicensePlate($faker->ean8());
                $vehicle->setType($faker->word());
                $vehicle->setModel($faker->word());
                $vehicle->setCapacity($faker->randomFloat(2, 1, 10));

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
