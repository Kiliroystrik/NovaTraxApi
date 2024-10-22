<?php

namespace App\Controller\API;

use App\DataFixtures\ClientFixtures;
use App\DataFixtures\DriverFixtures;
use App\DataFixtures\CompanyFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Driver;
use App\Entity\User;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DriverControllerTest extends WebTestCase
{
    private $entityManager;
    private $client;
    private $databaseTool;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->loadFixturesDependences();
    }

    private function loadFixturesDependences(): void
    {
        $this->databaseTool->loadFixtures([
            CompanyFixtures::class,
            UserFixtures::class,
            DriverFixtures::class
        ]);
    }

    /**
     * Create a client with a default Authorization header.
     *
     * @param string $username
     * @param string $password
     */
    protected function createAuthenticatedClient($username = 'superadmin@gmail.com', $password = 'password')
    {
        $this->client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => $username,
                'password' => $password,
            ])
        );

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));

        return $this->client;
    }

    public function testGetDrivers(): void
    {
        // Authentification
        // $this->createAuthenticatedClient("admin@gmail.com", "password");
        $this->createAuthenticatedClient();

        // Envoyer une requête GET à l'API pour récupérer les entreprises
        $this->client->request('GET', '/api/drivers');

        // Vérifier que la réponse HTTP est bien 200 OK
        $this->assertResponseStatusCodeSame(200);

        // Vérifier que le contenu de la réponse est du JSON
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        // Extraire le contenu JSON de la réponse
        $responseContent = $this->client->getResponse()->getContent();
        $data = json_decode($responseContent, true);

        // Je vérifie que ma réponse contienne la pagination
        $this->assertArrayHasKey('items', $data);
        $this->assertArrayHasKey('totalItems', $data);
        $this->assertArrayHasKey('currentPage', $data);
        $this->assertArrayHasKey('totalPages', $data);

        // Je récupère la partie items de ma requête
        $drivers = $data['items'];

        // Je récupère la quantité totale des commandes des clients
        $totalDrivers = $data['totalItems'];

        // Je récupère les numéros des commandes des clients
        // $driversNumbers = array_column($drivers, 'driverNumber');

        // Récupérer toutes les entreprises de la base de données
        $repository = $this->entityManager->getRepository(Driver::class);
        $expectedDrivers = $repository->findAll();

        // Vérifier que la quantité totale des commandes des clients est correcte
        $this->assertCount($totalDrivers, $expectedDrivers);

        // Vérifier que chaque entreprise des fixtures est présente dans la réponse
        // foreach ($expectedDrivers as $driver) {
        //     $this->assertContains($driver->getDriverNumber(), $driversNumbers, 'Driver number not found in response');
        // }
    }

    public function testGetDriver(): void
    {
        // Authentification
        $client = $this->createAuthenticatedClient();

        // Récupérer un conducteur de mon utilisateur
        $driverRepository = $this->entityManager->getRepository(Driver::class);
        $driver = $driverRepository->findAll()[0];

        // Envoyer une requête GET à l'API pour récupérer le conducteur
        $client->request('GET', "/api/drivers/{$driver->getId()}");

        // Vérifier que la réponse HTTP est bien 200 OK
        $this->assertResponseStatusCodeSame(200);

        // Vérifier que le contenu de la réponse est du JSON
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        // Extraire le contenu JSON de la réponse
        $responseContent = $client->getResponse()->getContent();
        $data = json_decode($responseContent, true);

        // Vérifier que la réponse contient les informations de le conducteur
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('firstName', $data);
        $this->assertArrayHasKey('lastName', $data);
    }

    public function testCreateDriver(): void
    {
        // Authentification avec un utilisateur spécifique
        $client = $this->createAuthenticatedClient("admin@gmail.com", "password");

        // Récupérer un utilisateur pour vérifier l'association avec le conducteur
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'admin@gmail.com']);

        // Récupérer la compagnie de l'utilisateur
        $userCompany = $user->getCompany();

        // Envoyer une requête POST à l'API pour ajouter un conducteur
        $client->request(
            'POST',
            '/api/drivers',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'firstName' => 'Driver 1 first name',
                'lastName' => 'Driver 1 last name',
                'licenseNumber' => '123456789',
            ])
        );

        // Vérifier que la requête HTTP est bien 201 Created
        $this->assertResponseStatusCodeSame(201);

        // Vérifier que le contenu de la réponse est du JSON
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        // Extraire le contenu JSON de la requête
        $responseContent = $client->getResponse()->getContent();
        $data = json_decode($responseContent, true);

        // Vérifier que la réponse contient les informations de le conducteur
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('firstName', $data); // Vérifier que l'driverNumber est généré
        $this->assertArrayHasKey('lastName', $data);
        $this->assertArrayHasKey('licenseNumber', $data);

        // Vérifier que le conducteur est associé à la compagnie de l'utilisateur
        $this->assertSame($userCompany->getName(), $data['company']['name']);
    }


    public function testUpdateDriver(): void
    {
        // Authentification
        $client = $this->createAuthenticatedClient("admin@gmail.com", "password");

        // Récupérer un conducteur de mon utilisateur
        $driverRepository = $this->entityManager->getRepository(Driver::class);
        $driver = $driverRepository->findAll()[0];

        // Envoyer une requête PUT ou PATCH à l'API pour mettre à jour le conducteur
        $client->request(
            'PATCH',
            "/api/drivers/{$driver->getId()}",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'firstName' => 'Driver 2 first name',
                'lastName' => 'Driver 2 last name',
                'licenseNumber' => '123456789',
            ])
        );

        // Vérifier que la requête HTTP est bien 200 OK
        $this->assertResponseStatusCodeSame(200);

        // Vérifier que le contenu de la requête est du JSON
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        // Extraire le contenu JSON de la requête
        $responseContent = $client->getResponse()->getContent();
        $data = json_decode($responseContent, true);

        // Vérifier que le conducteur est mise à jour
        $this->assertSame('Driver 2 first name', $data['firstName']);
        $this->assertSame('Driver 2 last name', $data['lastName']);
        $this->assertSame('123456789', $data['licenseNumber']);
    }


    public function testDeleteDriver(): void
    {
        // Authentification
        $client = $this->createAuthenticatedClient("admin@gmail.com", "password");

        // Récupérer un conducteur de mon utilisateur
        $driverRepository = $this->entityManager->getRepository(Driver::class);
        $driver = $driverRepository->findAll()[0];

        // Envoyer une requête DELETE à l'API pour supprimer le conducteur
        $client->request(
            'DELETE',
            "/api/drivers/{$driver->getId()}"
        );

        // Vérifier que la requête HTTP est bien 204 No Content
        $this->assertResponseStatusCodeSame(204);

        // Récupérer à nouveau le conducteur pour vérifier qu'il est supprimé
        $deletedDriver = $driverRepository->find($driver->getId());
        $this->assertNull($deletedDriver);
    }


    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->databaseTool);
        $this->entityManager->close();
        $this->entityManager = null; // éviter les fuites de mémoire
    }
}
