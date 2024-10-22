<?php

namespace App\DataFixtures;

use App\Entity\ClientOrder;
use App\Entity\Company;
use App\Entity\Client;
use App\Entity\Status;
use App\Enum\StatusName;
use App\Service\PasswordHashService;
use App\Service\SerialNumberGeneratorService;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

class ClientOrderFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private PasswordHashService $passwordHashService, private SerialNumberGeneratorService $serialNumberGenerator) {}

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create('fr_FR');

        // Définir les statuts disponibles
        $statuses = [
            StatusName::PENDING,
            StatusName::CONFIRMED,
            StatusName::CANCELLED,
            StatusName::COMPLETED,
        ];

        // Compteur pour les références uniques
        $breaker = 0;

        // Boucle sur chaque entreprise
        for ($i = 0; $i < 10; $i++) {
            // Récupérer chaque entreprise par référence
            /** @var Company $randomCompany */
            $randomCompany = $this->getReference('company-' . $i, Company::class);

            // Pour chaque entreprise, associer des commandes à ses clients
            for ($j = 0; $j < 10; $j++) {
                /** @var Client $client */
                $client = $this->getReference('client-' . $breaker, Client::class);

                // Création de la commande
                $clientOrder = new ClientOrder();
                $clientOrder->setCompany($randomCompany);
                $clientOrder->setClient($client);

                // Numéro de commande
                $clientOrderNumber = $this->serialNumberGenerator->generateOrderNumber();
                $clientOrder->setOrderNumber($clientOrderNumber);

                // Date de livraison prévue
                $clientOrder->setExpectedDeliveryDate(DateTimeImmutable::createFromMutable($faker->dateTimeThisYear()));

                // Sélectionner un statut aléatoire
                $statusName = $faker->randomElement($statuses);
                $statusReference = "status_ClientOrder_{$statusName}";

                /** @var Status $status */
                $status = $this->getReference($statusReference);
                $clientOrder->setStatus($status);

                // Persister la commande dans l'EntityManager
                $manager->persist($clientOrder);

                // Ajouter une référence unique pour la commande
                $this->addReference('clientOrder-' . $breaker, $clientOrder);

                $breaker++;
            }
        }

        // Sauvegarder toutes les commandes en base
        $manager->flush();
    }

    // Dépendances de cette fixture
    public function getDependencies(): array
    {
        return [
            CompanyFixtures::class,
            ClientFixtures::class,
            StatusFixtures::class,
        ];
    }
}
