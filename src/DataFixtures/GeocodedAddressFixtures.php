<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\GeocodedAddress;
use App\Service\PasswordHashService;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

class GeocodedAddressFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private PasswordHashService $passwordHashService) {}

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create('fr_FR');

        $breaker = 0;

        for ($i = 0; $i < 10; $i++) {
            $company = $this->getReference('company-' . $i, Company::class);
            for ($i = 0; $i < 100; $i++) {
                $geocodedAddress = new GeocodedAddress();
                $geocodedAddress->setFullAddress($faker->address);
                $geocodedAddress->setLatitude($faker->latitude);
                $geocodedAddress->setLongitude($faker->longitude);
                $geocodedAddress->setCity($faker->city);
                $geocodedAddress->setPostalCode($faker->postcode);
                $geocodedAddress->setDepartment($faker->countryCode);
                $geocodedAddress->setCountry($faker->country);
                $geocodedAddress->setSource('api');
                $geocodedAddress->setStreetName($faker->streetName);
                $geocodedAddress->setStreetNumber($faker->buildingNumber);
                $geocodedAddress->setCreatedAt(new DateTimeImmutable());
                $geocodedAddress->setUpdatedAt(new DateTimeImmutable());
                $geocodedAddress->setCompany($company);

                $manager->persist($geocodedAddress);

                $this->addReference('geocoded-address-' . $breaker, $geocodedAddress);

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
