<?php

namespace App\DataFixtures;

use App\Entity\VehicleAvailability;
use App\Service\PasswordHashService;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

class VehicleAvailabilityFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private PasswordHashService $passwordHashService) {}

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create('fr_FR');

        for ($i = 0; $i < 300; $i++) {
            $vehicle = $this->getReference('vehicle-' . $i);
            $vehicleAvailability = new VehicleAvailability();
            $vehicleAvailability->setVehicle($vehicle);
            $vehicleAvailability->setStartDate(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year', '+1 year')));
            $vehicleAvailability->setEndDate(DateTimeImmutable::createFromMutable($faker->dateTimeBetween($vehicleAvailability->getStartDate()->format('Y-m-d'), '+1 year')));
            $vehicleAvailability->setReason($faker->text());
            $manager->persist($vehicleAvailability);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            VehicleFixtures::class
        ];
    }
}
