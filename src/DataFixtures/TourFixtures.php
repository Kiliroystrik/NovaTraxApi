<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\ClientOrder;
use App\Entity\Driver;
use App\Entity\Tour;
use App\Entity\Vehicle;
use App\Service\PasswordHashService;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

class TourFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private PasswordHashService $passwordHashService) {}

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create('fr_FR');

        for ($i = 0; $i < 10; $i++) {
            $company = $this->getReference('company-' . $i, Company::class);

            $clientOrderRepository = $manager->getRepository(ClientOrder::class);
            $clientOrders = $clientOrderRepository->findOneBy(['company' => $company]);

            foreach ($clientOrders as $clientOrder) {

                $deliveries = $clientOrder->getDeliveries();

                $tour = new Tour();
                foreach ($deliveries as $delivery) {
                    $tour->addDelivery($delivery);
                }

                $tour->setCompany($company);
                $tour->setDepartureDate(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year', '+1 year')));
                $tour->setExpectedArrivalDate(DateTimeImmutable::createFromMutable($faker->dateTimeBetween($tour->getDepartureDate()->format('Y-m-d'), '+1 year')));

                $tour->setStatus($faker->randomElement(['pending', 'delivered', 'cancelled', 'in_transit']));

                $driverRepository = $manager->getRepository(Driver::class);
                $drivers = $driverRepository->findBy(['company' => $company]);
                $tour->setDriver($faker->randomElement($drivers));

                $vehicleRepository = $manager->getRepository(Vehicle::class);
                $vehicles = $vehicleRepository->findBy(['company' => $company]);
                $tour->setVehicle($faker->randomElement($vehicles));

                $manager->persist($tour);
            }

            $tour = new Tour();
            $tour->setCompany($company);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            DeliveryProductFixtures::class
        ];
    }
}
