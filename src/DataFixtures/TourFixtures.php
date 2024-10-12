<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\ClientOrder;
use App\Entity\Driver;
use App\Entity\Tour;
use App\Entity\Vehicle;
use App\Service\SerialNumberGeneratorService;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

class TourFixtures extends Fixture implements DependentFixtureInterface
{
    // src/DataFixtures/TourFixtures.php

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create('fr_FR');
        $serialNumberGeneratorService = new SerialNumberGeneratorService();

        for ($i = 0; $i < 10; $i++) { // Pour chaque Company
            /** @var Company $company */
            $company = $this->getReference('company-' . $i);

            // Récupère toutes les commandes clients pour la compagnie
            $clientOrderRepository = $manager->getRepository(ClientOrder::class);
            $clientOrders = $clientOrderRepository->findBy(['company' => $company]);

            foreach ($clientOrders as $clientOrder) {
                $deliveries = $clientOrder->getDeliveries();

                $tour = new Tour();

                // Génère un numéro de tournée
                $tourOrderNumber = $serialNumberGeneratorService->generateTourNumber();
                $tour->setTourNumber($tourOrderNumber);

                // Ajoute toutes les livraisons à la tournée
                foreach ($deliveries as $delivery) {
                    $tour->addDelivery($delivery);
                }

                // Définit la compagnie de la tournée
                $tour->setCompany($company);

                // Définit les dates de début et de fin
                $startDate = DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 months', '+1 months'));
                $endDate = $startDate->modify('+3 days');
                $tour->setStartDate($startDate);
                $tour->setEndDate($endDate);

                // Définit le statut de la tournée
                $tour->setStatus($faker->randomElement(['pending', 'delivered', 'cancelled', 'in_transit']));

                // Récupère et définit un conducteur aléatoire pour la compagnie
                $driverRepository = $manager->getRepository(Driver::class);
                $drivers = $driverRepository->findBy(['company' => $company]);
                if (!empty($drivers)) {
                    $tour->setDriver($faker->randomElement($drivers));
                }

                // Récupère et définit un véhicule aléatoire pour la compagnie
                $vehicleRepository = $manager->getRepository(Vehicle::class);
                $vehicles = $vehicleRepository->findBy(['company' => $company]);
                if (!empty($vehicles)) {
                    $tour->setVehicle($faker->randomElement($vehicles));
                }

                // Je récupère les warehouse de la companie actuelle
                $warehouses = $company->getWarehouses();
                $tour->setLoading($faker->randomElement($warehouses));

                // Persister la tournée
                $manager->persist($tour);
            }
        }

        $manager->flush();
    }


    public function getDependencies()
    {
        return [
            DeliveryProductFixtures::class,
            CompanyFixtures::class,
            ClientOrderFixtures::class,
            DriverFixtures::class,
            VehicleFixtures::class,
        ];
    }
}
