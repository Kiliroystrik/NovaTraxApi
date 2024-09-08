<?php

namespace App\DataFixtures;

use App\Entity\Driver;
use App\Service\PasswordHashService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

class DriverFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private PasswordHashService $passwordHashService) {}

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create('fr_FR');

        $breaker = 0;

        for ($i = 0; $i < 10; $i++) {
            $company = $this->getReference('company-' . $i);

            for ($j = 0; $j < 30; $j++) {
                $driver = new Driver();
                $driver->setCompany($company);
                $driver->setFirstName($faker->firstName());
                $driver->setLastName($faker->lastName());
                $driver->setLicenseNumber($faker->ean8());

                $manager->persist($driver);

                $this->setReference('driver-' . $breaker, $driver);

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
