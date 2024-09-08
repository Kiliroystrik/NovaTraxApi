<?php

namespace App\DataFixtures;

use App\Entity\DriverAvailability;
use App\Service\PasswordHashService;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

class DriverAvailabilityFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private PasswordHashService $passwordHashService) {}

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create('fr_FR');

        for ($i = 0; $i < 300; $i++) {
            $driver = $this->getReference('driver-' . $i);
            $driverAvailability = new DriverAvailability();
            $driverAvailability->setDriver($driver);
            $driverAvailability->setStartDate(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year', '+1 year')));
            $driverAvailability->setEndDate(DateTimeImmutable::createFromMutable($faker->dateTimeBetween($driverAvailability->getStartDate()->format('Y-m-d'), '+1 year')));
            $driverAvailability->setReason($faker->text());
            $manager->persist($driverAvailability);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            DriverFixtures::class
        ];
    }
}
