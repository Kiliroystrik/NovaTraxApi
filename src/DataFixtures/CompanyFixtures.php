<?php

namespace App\DataFixtures;

use Faker\Factory as FakerFactory;
use App\Entity\Company;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CompanyFixtures extends Fixture
{
    private array $companies = [];
    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create('fr_FR');

        // Init SuperAdmin company
        $superAdminCompany = new Company();
        $superAdminCompany->setName('SuperAdmin Company');
        $superAdminCompany->setContactEmail($faker->email());
        $superAdminCompany->setContactPhone($faker->phoneNumber());

        $manager->persist($superAdminCompany);

        // Init random companies
        for ($i = 0; $i < 10; $i++) {
            $company = new Company();
            $company->setName($faker->company());
            $company->setContactEmail($faker->email());
            $company->setContactPhone($faker->phoneNumber());
            $manager->persist($company);

            // Ajouter une référence pour chaque entreprise
            $this->addReference('company-' . $i, $company);
            $this->addCompany($company);
        }

        $manager->flush();

        // Ajouter la référence pour la SuperAdmin Company
        $this->addReference('super-admin-company', $superAdminCompany);
    }

    public function getCompanies(): array
    {
        return $this->companies;
    }

    private function addCompany(Company $company)
    {
        $this->companies[] = $company;
    }
}
