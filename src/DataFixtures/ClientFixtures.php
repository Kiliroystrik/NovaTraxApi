<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Company;
use App\Service\PasswordHashService;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

class ClientFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private PasswordHashService $passwordHashService) {}

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create('fr_FR');

        // Me permet de ne pas avoir de doublon
        $breaker = 0;

        for ($i = 0; $i < 10; $i++) {
            $randomCompany = $this->getReference('company-' . $i, Company::class); // Récupérer chaque entreprise par référence
            for ($j = 0; $j < 10; $j++) {
                $client = new Client();
                $client->setCompany($randomCompany);
                $client->setName($faker->company());


                $manager->persist($client);

                $this->addReference('client-' . $breaker, $client);

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
