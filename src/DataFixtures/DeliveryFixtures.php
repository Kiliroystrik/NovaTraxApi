<?php

namespace App\DataFixtures;

use App\Entity\ClientOrder;
use App\Entity\Delivery;
use App\Entity\GeocodedAddress;
use App\Entity\Status;
use App\Enum\StatusName;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

class DeliveryFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create('fr_FR');

        // Définir les statuts valides pour les Livraisons
        $DeliveryStatuses = [
            StatusName::PENDING,
            StatusName::SCHEDULED,
            StatusName::IN_TRANSIT,
            StatusName::DELIVERED,
            StatusName::FAILED,
        ];

        $breaker = 0;

        for ($i = 0; $i < 100; $i++) {
            /** @var ClientOrder $randomClientOrder */
            $randomClientOrder = $this->getReference('clientOrder-' . $i, ClientOrder::class);

            for ($j = 0; $j < 30; $j++) {
                $delivery = new Delivery();
                $delivery->setClientOrder($randomClientOrder);
                $delivery->setCompany($randomClientOrder->getCompany());

                // Date de livraison prévue
                $clientOrderDate = $randomClientOrder->getExpectedDeliveryDate()->format('Y-m-d');
                $expectedDeliveryDate = DateTimeImmutable::createFromMutable($faker->dateTimeBetween($clientOrderDate, '+1 year'));
                $delivery->setExpectedDeliveryDate($expectedDeliveryDate);

                // Date de livraison effective
                $expectedDeliveryDateToString = $expectedDeliveryDate->format('Y-m-d');
                $actualDeliveryDate = DateTimeImmutable::createFromMutable($faker->dateTimeBetween($expectedDeliveryDateToString, '+1 year'));
                $delivery->setActualDeliveryDate($actualDeliveryDate);

                // Sélectionner un statut aléatoire pour la livraison
                $statusName = $faker->randomElement($DeliveryStatuses);
                $statusReference = "status_Delivery_{$statusName}";

                /** @var Status $status */
                $status = $this->getReference($statusReference);
                $delivery->setStatus($status);

                // Assignation de l'adresse géocodée
                $randomGeocodedAddress = $this->getReference('geocoded-address-' . rand(0, 99), GeocodedAddress::class);
                $delivery->setGeocodedAddress($randomGeocodedAddress);

                $manager->persist($delivery);

                // Ajouter une référence unique pour cette livraison
                $this->addReference('delivery-' . $breaker, $delivery);
                $breaker++;
            }
        }

        // Exécuter le flush une seule fois pour optimiser les performances
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            ClientOrderFixtures::class,
            GeocodedAddressFixtures::class,
            StatusFixtures::class, // Assurez-vous que StatusFixtures est bien inclus
        ];
    }
}
