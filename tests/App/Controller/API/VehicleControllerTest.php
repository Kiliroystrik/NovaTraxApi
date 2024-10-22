<?php

namespace App\Controller\API;

use App\DataFixtures\ClientFixtures;
use App\DataFixtures\VehicleFixtures;
use App\DataFixtures\CompanyFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Vehicle;
use App\Entity\User;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class VehicleControllerTest extends WebTestCase
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
            VehicleFixtures::class
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

    public function testGetVehicles(): void
    {
        // Authentification
        // $this->createAuthenticatedClient("admin@gmail.com", "password");
        $this->createAuthenticatedClient();

        // Envoyer une requête GET à l'API pour récupérer les entreprises
        $this->client->request('GET', '/api/vehicles');

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
        $vehicles = $data['items'];

        // Je récupère la quantité totale des commandes des clients
        $totalVehicles = $data['totalItems'];

        // Je récupère les numéros des commandes des clients
        // $vehiclesNumbers = array_column($vehicles, 'vehicleNumber');

        // Récupérer toutes les entreprises de la base de données
        $repository = $this->entityManager->getRepository(Vehicle::class);
        $expectedVehicles = $repository->findAll();

        // Vérifier que la quantité totale des commandes des clients est correcte
        $this->assertCount($totalVehicles, $expectedVehicles);

        // Vérifier que chaque entreprise des fixtures est présente dans la réponse
        // foreach ($expectedVehicles as $vehicle) {
        //     $this->assertContains($vehicle->getVehicleNumber(), $vehiclesNumbers, 'Vehicle number not found in response');
        // }
    }

    public function testGetVehicle(): void
    {
        // Authentification
        $client = $this->createAuthenticatedClient();

        // Récupérer un vehicule de mon utilisateur
        $vehicleRepository = $this->entityManager->getRepository(Vehicle::class);
        $vehicle = $vehicleRepository->findAll()[0];

        // Envoyer une requête GET à l'API pour récupérer le vehicule
        $client->request('GET', "/api/vehicles/{$vehicle->getId()}");

        // Vérifier que la réponse HTTP est bien 200 OK
        $this->assertResponseStatusCodeSame(200);

        // Vérifier que le contenu de la réponse est du JSON
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        // Extraire le contenu JSON de la réponse
        $responseContent = $client->getResponse()->getContent();
        $data = json_decode($responseContent, true);

        // Vérifier que la réponse contient les informations de le vehicule
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('capacity', $data);
        $this->assertArrayHasKey('licensePlate', $data);
        $this->assertArrayHasKey('model', $data);
        $this->assertArrayHasKey('type', $data);
    }

    public function testCreateVehicle(): void
    {
        // Authentification avec un utilisateur spécifique
        $client = $this->createAuthenticatedClient("admin@gmail.com", "password");

        // Récupérer un utilisateur pour vérifier l'association avec le vehicule
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'admin@gmail.com']);

        // Récupérer la compagnie de l'utilisateur
        $userCompany = $user->getCompany();

        // Envoyer une requête POST à l'API pour ajouter un vehicule
        $client->request(
            'POST',
            '/api/vehicles',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'capacity' => '100',
                'licensePlate' => 'ABC123',
                'model' => 'Model 1',
                'type' => 'Car',
            ])
        );

        // Vérifier que la requête HTTP est bien 201 Created
        $this->assertResponseStatusCodeSame(201);

        // Vérifier que le contenu de la réponse est du JSON
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        // Extraire le contenu JSON de la requête
        $responseContent = $client->getResponse()->getContent();
        $data = json_decode($responseContent, true);

        // Vérifier que la réponse contient les informations de le vehicule
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('capacity', $data);
        $this->assertArrayHasKey('licensePlate', $data);
        $this->assertArrayHasKey('model', $data);
        $this->assertArrayHasKey('type', $data);

        // Vérifier que le vehicule est associé à la compagnie de l'utilisateur
        $this->assertSame($userCompany->getName(), $data['company']['name']);
    }


    public function testUpdateVehicle(): void
    {
        // Authentification
        $client = $this->createAuthenticatedClient("admin@gmail.com", "password");

        // Récupérer un vehicule de mon utilisateur
        $vehicleRepository = $this->entityManager->getRepository(Vehicle::class);
        $vehicle = $vehicleRepository->findAll()[0];

        // Envoyer une requête PUT ou PATCH à l'API pour mettre à jour le vehicule
        $client->request(
            'PATCH',
            "/api/vehicles/{$vehicle->getId()}",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'capacity' => '101',
                'licensePlate' => '123451789',
                'model' => 'Vehicle 2 model',
                'type' => 'truck',
            ])
        );

        // Vérifier que la requête HTTP est bien 200 OK
        $this->assertResponseStatusCodeSame(200);

        // Vérifier que le contenu de la requête est du JSON
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        // Extraire le contenu JSON de la requête
        $responseContent = $client->getResponse()->getContent();
        $data = json_decode($responseContent, true);

        // Vérifier que le vehicule est mise à jour
        $this->assertSame('101', $data['capacity']);
        $this->assertSame('123451789', $data['licensePlate']);
        $this->assertSame('Vehicle 2 model', $data['model']);
        $this->assertSame('truck', $data['type']);
    }


    public function testDeleteVehicle(): void
    {
        // Authentification
        $client = $this->createAuthenticatedClient("admin@gmail.com", "password");

        // Récupérer un vehicule de mon utilisateur
        $vehicleRepository = $this->entityManager->getRepository(Vehicle::class);
        $vehicle = $vehicleRepository->findAll()[0];

        // Envoyer une requête DELETE à l'API pour supprimer le vehicule
        $client->request(
            'DELETE',
            "/api/vehicles/{$vehicle->getId()}"
        );

        // Vérifier que la requête HTTP est bien 204 No Content
        $this->assertResponseStatusCodeSame(204);

        // Récupérer à nouveau le vehicule pour vérifier qu'il est supprimé
        $deletedVehicle = $vehicleRepository->find($vehicle->getId());
        $this->assertNull($deletedVehicle);
    }


    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->databaseTool);
        $this->entityManager->close();
        $this->entityManager = null; // éviter les fuites de mémoire
    }
}
