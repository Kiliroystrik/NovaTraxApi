<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\GeocodedAddress;
use App\Entity\Vehicle;
use App\Entity\Warehouse;
use App\Service\PasswordHashService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

class WarehouseFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private PasswordHashService $passwordHashService) {}

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create('fr_FR');

        $breaker = 0;

        for ($i = 0; $i < 10; $i++) {
            $company = $this->getReference('company-' . $i, Company::class);

            for ($j = 0; $j < 10; $j++) {

                $geocodedAddress = new GeocodedAddress();
                $geocodedAddress->setCompany($company);
                $geocodedAddress->setFullAddress($faker->address());
                $geocodedAddress->setLatitude($faker->latitude());
                $geocodedAddress->setLongitude($faker->longitude());
                $geocodedAddress->setCity($faker->city());
                $geocodedAddress->setPostalCode($faker->postcode());
                $geocodedAddress->setDepartment($faker->countryCode());
                $geocodedAddress->setCountry($faker->country());
                $geocodedAddress->setSource('api');
                $geocodedAddress->setStreetName($faker->streetName());
                $geocodedAddress->setStreetNumber($faker->buildingNumber());
                $geocodedAddress->setCreatedAt(new \DateTimeImmutable());
                $geocodedAddress->setUpdatedAt(new \DateTimeImmutable());

                $manager->persist($geocodedAddress);

                $warehouse = new Warehouse();

                $warehouse->setCompany($company);
                $warehouse->setName($faker->company());

                $warehouse->setAddress($geocodedAddress);

                $this->addReference('warehouse-' . $breaker, $warehouse);

                $manager->persist($warehouse);

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
