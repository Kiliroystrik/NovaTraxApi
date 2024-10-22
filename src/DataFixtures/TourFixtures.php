<?php

namespace App\DataFixtures;

use App\Entity\ClientOrder;
use App\Entity\Delivery;
use App\Entity\Tour;
use App\Entity\Company;
use App\Entity\Driver;
use App\Entity\Vehicle;
use App\Entity\Warehouse;
use App\Entity\Status;
use App\Enum\StatusName;
use App\Service\SerialNumberGeneratorService;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

class TourFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private SerialNumberGeneratorService $serialNumberGeneratorService) {}

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create('fr_FR');

        for ($i = 0; $i < 10; $i++) { // Pour chaque Company
            /** @var Company $company */
            $company = $this->getReference('company-' . $i);

            // Récupère toutes les commandes clients pour la compagnie
            $clientOrderRepository = $manager->getRepository(ClientOrder::class);
            $clientOrders = $clientOrderRepository->findBy(['company' => $company]);

            foreach ($clientOrders as $clientOrder) {
                $deliveries = $clientOrder->getDeliveries();

                if (count($deliveries) === 0) {
                    continue; // Pas de livraisons pour cette commande
                }

                // Convertir la collection Doctrine en tableau avant d'appliquer array_filter
                $deliveriesArray = $deliveries->toArray();

                // Filtrage aléatoire des livraisons à associer à une tournée
                $deliveriesToAssign = array_values(array_filter($deliveriesArray, function ($delivery) use ($faker) {
                    return $faker->boolean(66); // Assigne environ 2/3 des livraisons à une tournée
                }));

                if (count($deliveriesToAssign) === 0) {
                    continue; // Pas de livraisons pour cette commande à inclure dans une tournée
                }

                // Créer la tournée
                $tour = new Tour();

                // Génère un numéro de tournée
                $tourOrderNumber = $this->serialNumberGeneratorService->generateTourNumber();
                $tour->setTourNumber($tourOrderNumber);

                // Variables pour les dates et le type de produit
                $startDate = null;
                $endDate = null;
                $firstProductType = null;

                // Ajoute les livraisons à la tournée en vérifiant le type de produit
                foreach ($deliveriesToAssign as $delivery) {
                    @
                    /** @var Delivery $delivery */
                    $deliveryProduct = $delivery->getProductDeliveries()->first();
                    if ($deliveryProduct) {
                        $productType = $deliveryProduct->getProduct()->getType();

                        if (!$firstProductType) {
                            $firstProductType = $productType; // Définir le type de la première livraison
                        } elseif ($productType !== $firstProductType) {
                            // Si le type de produit change, arrêter d'ajouter des livraisons
                            break;
                        }

                        // Ajouter la livraison à la tournée
                        $tour->addDelivery($delivery);

                        // Mettre à jour les dates de début et de fin
                        $deliveryDate = $delivery->getExpectedDeliveryDate();
                        if (!$startDate || $deliveryDate < $startDate) {
                            $startDate = $deliveryDate;
                        }
                        if (!$endDate || $deliveryDate > $endDate) {
                            $endDate = $deliveryDate;
                        }
                    }
                }

                // Si aucune livraison valide n'a été ajoutée, passer à la commande suivante
                if (count($tour->getDeliveries()) === 0) {
                    continue;
                }

                // Définit la compagnie de la tournée
                $tour->setCompany($company);

                // Définit les dates de début et de fin basées sur les livraisons
                $tour->setStartDate($startDate);
                $tour->setEndDate($endDate);

                // Sélectionner un statut aléatoire pour la tournée
                $statusName = $faker->randomElement([
                    StatusName::PLANNED,
                    StatusName::IN_PROGRESS,
                    StatusName::COMPLETED_TOUR,
                    StatusName::CANCELLED_TOUR,
                ]);
                $statusReference = "status_Tour_{$statusName}";

                /** @var Status $status */
                $status = $this->getReference($statusReference);
                $tour->setStatus($status);

                // Récupère et définit un conducteur aléatoire pour la compagnie
                $driverRepository = $manager->getRepository(Driver::class);
                $drivers = $driverRepository->findBy(['company' => $company]);
                if (!empty($drivers)) {
                    /** @var Driver $driver */
                    $driver = $faker->randomElement($drivers);
                    $tour->setDriver($driver);
                }

                // Récupère et définit un véhicule aléatoire pour la compagnie
                $vehicleRepository = $manager->getRepository(Vehicle::class);
                $vehicles = $vehicleRepository->findBy(['company' => $company]);
                if (!empty($vehicles)) {
                    /** @var Vehicle $vehicle */
                    $vehicle = $faker->randomElement($vehicles);
                    $tour->setVehicle($vehicle);
                }

                // Récupère les entrepôts de la compagnie actuelle
                $warehouses = $company->getWarehouses();
                if (count($warehouses) > 0) {
                    /** @var Warehouse $warehouse */
                    $warehouse = $faker->randomElement($warehouses);
                    $tour->setLoading($warehouse);
                }

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
            StatusFixtures::class,
        ];
    }
}
