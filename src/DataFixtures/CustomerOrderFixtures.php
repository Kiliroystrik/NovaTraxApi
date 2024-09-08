<?php

namespace App\DataFixtures;

use App\Entity\CustomerOrder;
use App\Entity\Delivery;
use App\Service\PasswordHashService;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

class CustomerOrderFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private PasswordHashService $passwordHashService) {}

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create('fr_FR');

        $statuses = ['pending', 'delivered', 'cancelled'];

        // Me permet de ne pas avoir de doublon
        $breaker = 0;

        for ($i = 0; $i < 10; $i++) {
            $randomCompany = $this->getReference('company-' . $i); // Récupérer chaque entreprise par référence
            for ($j = 0; $j < 10; $j++) {
                $customerOrder = new CustomerOrder();
                $customerOrder->setCompany($randomCompany);
                $customerOrder->setCustomerName($faker->name);
                $customerOrder->setOrderDate(DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 year', '+1 year')));
                $orderDateToString = $customerOrder->getOrderDate()->format('Y-m-d');
                $customerOrder->setExpectedDeliveryDate(DateTimeImmutable::createFromMutable($faker->dateTimeBetween($orderDateToString, '+1 year')));
                $customerOrder->setStatus($faker->randomElement($statuses));

                $manager->persist($customerOrder);

                $this->addReference('customer-order-' . $breaker, $customerOrder);

                $breaker++;
            }
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CompanyFixtures::class,
        ];
    }
}
