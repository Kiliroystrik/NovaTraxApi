<?php

namespace App\DataFixtures;

use App\Entity\Delivery;
use App\Service\PasswordHashService;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

class DeliveryFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private PasswordHashService $passwordHashService) {}

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create('fr_FR');

        $DeliveryStatuses = ['pending', 'delivered', 'cancelled'];

        $breaker = 0;

        // J'ai 30 customerOrders donc je déclare for ($i = 0; $i < 30; $i++)
        for ($i = 0; $i < 30; $i++) {
            $randomCustomerOrder = $this->getReference('customer-order-' . $i);

            for ($j = 0; $j < 3; $j++) {
                $delivery = new Delivery();
                $delivery->setCustomerOrder($randomCustomerOrder);
                $delivery->setCompany($randomCustomerOrder->getCompany());
                $customerOrderDate = $randomCustomerOrder->getOrderDate()->format('Y-m-d');
                $expectedDeliveryDate = DateTimeImmutable::createFromMutable($faker->dateTimeBetween($customerOrderDate, '+1 year'));
                $delivery->setExpectedDeliveryDate($expectedDeliveryDate);
                $expectedDeliveryDateToString = $expectedDeliveryDate->format('Y-m-d');
                $actualDeliveryDate = DateTimeImmutable::createFromMutable($faker->dateTimeBetween($expectedDeliveryDateToString, '+1 year'));
                $delivery->setActualDeliveryDate($actualDeliveryDate);
                $delivery->setStatus($faker->randomElement($DeliveryStatuses));

                // Je set la geocodedAddress de la livraison
                // Je fais un random sur un chiffre entre 0 et 99
                $randomGeocodedAddress = $this->getReference('geocoded-address-' . rand(0, 99));
                $delivery->setGeocodedAddress($randomGeocodedAddress);

                $manager->persist($delivery);

                $this->addReference('delivery-' . $breaker, $delivery);
                $breaker++;
            }
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CustomerOrderFixtures::class,
            GeocodedAddressFixtures::class,
        ];
    }
}