<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\Driver;
use App\Entity\Unavailability;
use App\Entity\Vehicle;
use App\Entity\Reason;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

class UnavailabilityFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create('fr_FR');

        // Nombre d'indisponibilités à créer
        $numberOfUnavailabilities = 20;

        for ($i = 0; $i < $numberOfUnavailabilities; $i++) {
            // Décider aléatoirement si l'indisponibilité est pour un conducteur ou un véhicule
            $isDriverUnavailable = $faker->boolean();

            if ($isDriverUnavailable) {
                // Sélectionner un conducteur aléatoire
                $driverIndex = $faker->numberBetween(0, 4); // Supposez 5 conducteurs
                $driverReference = 'driver-' . $driverIndex;
                /** @var Driver $driver */
                $driver = $this->getReference($driverReference, Driver::class);

                // Sélectionner une raison aléatoire pour les conducteurs
                $driverReasonNames = ['maladie', 'congé', 'formation'];
                $selectedReasonName = 'reason-driver-' . $faker->randomElement($driverReasonNames);
                /** @var Reason $reason */
                $reason = $this->getReference($selectedReasonName, Reason::class);

                // Créer l'indisponibilité
                $unavailability = new Unavailability();
                $unavailability->setDriver($driver);
                $unavailability->setVehicle(null); // Pas de véhicule associé
                $unavailability->setCompany($driver->getCompany());

                // Définir la date de début
                $startDate = DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 months', '+1 months'));
                // Définir la date de fin à 3 jours après la date de début
                $endDate = $startDate->modify('+3 days');

                $unavailability->setStartDate($startDate);
                $unavailability->setEndDate($endDate);
                $unavailability->setReason($reason);
                $unavailability->setCreatedAt(new DateTimeImmutable());

                $manager->persist($unavailability);
            } else {
                // Sélectionner un véhicule aléatoire
                $vehicleIndex = $faker->numberBetween(0, 4); // Supposez 5 véhicules
                $vehicleReference = 'vehicle-' . $vehicleIndex;
                /** @var Vehicle $vehicle */
                $vehicle = $this->getReference($vehicleReference, Vehicle::class);

                // Sélectionner une raison aléatoire pour les véhicules
                $vehicleReasonNames = ['maintenance', 'réparation', 'nettoyage'];
                $selectedReasonName = 'reason-vehicle-' . $faker->randomElement($vehicleReasonNames);
                /** @var Reason $reason */
                $reason = $this->getReference($selectedReasonName, Reason::class);

                // Créer l'indisponibilité
                $unavailability = new Unavailability();
                $unavailability->setDriver(null); // Pas de conducteur associé
                $unavailability->setVehicle($vehicle);
                $unavailability->setCompany($vehicle->getCompany());

                // Définir la date de début
                $startDate = DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 months', '+1 months'));
                // Définir la date de fin à 3 jours après la date de début
                $endDate = $startDate->modify('+3 days');

                $unavailability->setStartDate($startDate);
                $unavailability->setEndDate($endDate);
                $unavailability->setReason($reason);
                $unavailability->setCreatedAt(new DateTimeImmutable());

                $manager->persist($unavailability);
            }
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CompanyFixtures::class,
            DriverFixtures::class,
            VehicleFixtures::class,
            ReasonFixtures::class,
        ];
    }
}
