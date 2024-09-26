<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\ClientOrder;
use App\Entity\Client;
use App\Service\ClientOrderNumberGenerator;
use App\Service\PasswordHashService;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

class ClientOrderFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private PasswordHashService $passwordHashService, private ClientOrderNumberGenerator $clientOrderNumberGenerator) {}

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create('fr_FR');

        // Statuts possibles pour une commande
        $statuses = ['pending', 'delivered', 'cancelled'];

        // Compteur pour les références uniques
        $breaker = 0;

        // Boucle sur chaque entreprise
        for ($i = 0; $i < 10; $i++) {
            // Récupérer chaque entreprise par référence
            /** @var Company $randomCompany */
            $randomCompany = $this->getReference('company-' . $i);

            // Pour chaque entreprise, on va associer des commandes à ses clients
            for ($j = 0; $j < 10; $j++) {
                /** @var Client $client */
                $client = $this->getReference('client-' . $breaker);

                // Création de la commande
                $clientOrder = new ClientOrder();
                $clientOrder->setCompany($randomCompany);
                $clientOrder->setClient($client);

                // Numéro de commande
                $clientOrderNumber = $this->clientOrderNumberGenerator->generate();
                $clientOrder->setOrderNumber($clientOrderNumber);

                // Date de livraison prévue
                $clientOrder->setExpectedDeliveryDate(DateTimeImmutable::createFromMutable($faker->dateTimeThisYear()));

                // Statut de la commande
                $clientOrder->setStatus($faker->randomElement($statuses));

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
        ];
    }
}
