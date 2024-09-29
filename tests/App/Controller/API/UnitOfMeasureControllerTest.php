<?php

namespace App\Controller\API;

use App\DataFixtures\CompanyFixtures;
use App\DataFixtures\UnitOfMeasureFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\UnitOfMeasure;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UnitOfMeasureControllerTest extends WebTestCase
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

        $this->loadFixtures();
    }

    private function loadFixtures(): void
    {
        $this->databaseTool->loadFixtures([
            CompanyFixtures::class,
            UserFixtures::class,
            UnitOfMeasureFixtures::class,
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

    public function testGetAllUnitOfMeasures(): void
    {
        // Authentification
        $this->createAuthenticatedClient();

        // Envoyer une requête GET à l'API pour récupérer les unités de mesure
        $this->client->request('GET', '/api/unit_of_measures');

        // Vérifier que la réponse HTTP est bien 200 OK
        $this->assertResponseStatusCodeSame(200);

        // Vérifier que le contenu de la réponse est du JSON
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        // Extraire le contenu JSON de la réponse
        $responseContent = $this->client->getResponse()->getContent();
        $data = json_decode($responseContent, true);

        // Vérifier que la réponse contient la pagination
        $this->assertArrayHasKey('items', $data);
        $this->assertArrayHasKey('totalItems', $data);
        $this->assertArrayHasKey('currentPage', $data);
        $this->assertArrayHasKey('totalPages', $data);

        // Récupérer la partie items
        $unitOfMeasures = $data['items'];

        // Vérifier que la quantité totale des unités de mesure est correcte
        $repository = $this->entityManager->getRepository(UnitOfMeasure::class);
        $expectedUnitOfMeasures = $repository->findAll();
        $this->assertCount($data['totalItems'], $expectedUnitOfMeasures);
    }

    public function testGetUnitOfMeasure(): void
    {
        // Authentification
        $client = $this->createAuthenticatedClient();

        // Récupérer une unité de mesure
        $unitOfMeasureRepository = $this->entityManager->getRepository(UnitOfMeasure::class);
        $unitOfMeasure = $unitOfMeasureRepository->findAll()[0];

        // Envoyer une requête GET à l'API pour récupérer l'unité de mesure
        $client->request('GET', "/api/unit_of_measures/{$unitOfMeasure->getId()}");

        // Vérifier que la réponse HTTP est bien 200 OK
        $this->assertResponseStatusCodeSame(200);

        // Vérifier que le contenu de la réponse est du JSON
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        // Extraire le contenu JSON de la réponse
        $responseContent = $client->getResponse()->getContent();
        $data = json_decode($responseContent, true);

        // Vérifier que la réponse contient les informations de l'unité de mesure
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('symbol', $data);
    }

    public function testCreateUnitOfMeasureWithSuperAdminRole(): void
    {
        // Authentification avec un utilisateur ayant le rôle SUPER_ADMIN
        $client = $this->createAuthenticatedClient();

        // Envoyer une requête POST à l'API pour ajouter une unité de mesure
        $client->request(
            'POST',
            '/api/unit_of_measures',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'test',
                'symbol' => 't'
            ])
        );

        // Vérifier que la requête HTTP est bien 201 Created
        $this->assertResponseStatusCodeSame(201);

        // Vérifier que le contenu de la réponse est du JSON
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        // Extraire le contenu JSON de la requête
        $responseContent = $client->getResponse()->getContent();

        // Vérifier que le contenu n'est pas vide
        $this->assertNotEmpty($responseContent, 'Le contenu de la réponse est vide.');

        // Décoder le JSON
        $data = json_decode($responseContent, true);

        // Vérifier que le JSON a bien été décodé
        $this->assertIsArray($data, 'La réponse n\'est pas un tableau valide.');

        // Vérifier que la réponse contient les informations de l'unité de mesure
        $this->assertArrayHasKey('id', $data, 'L\'id n\'est pas présent dans la réponse.');
        $this->assertArrayHasKey('name', $data, 'Le nom n\'est pas présent dans la réponse.');
        $this->assertArrayHasKey('symbol', $data, 'Le symbole n\'est pas présent dans la réponse.');
    }

    public function testCreateUnitOfMeasureWithInsufficientRole(): void
    {
        // Authentification avec un utilisateur n'ayant pas le rôle SUPER_ADMIN
        $client = $this->createAuthenticatedClient("admin@gmail.com", "password");

        // Envoyer une requête POST à l'API pour essayer d'ajouter une unité de mesure
        $client->request(
            'POST',
            '/api/unit_of_measures',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'testt',
                'symbol' => 'tt'
            ])
        );

        // Vérifier que l'accès est refusé (403 Forbidden)
        $this->assertResponseStatusCodeSame(403);
    }

    public function testUpdateUnitOfMeasureWithAdminRole(): void
    {
        // Authentification avec un utilisateur ayant le rôle SUPER_ADMIN
        $client = $this->createAuthenticatedClient("superadmin@gmail.com", "password");

        // Récupérer une unité de mesure
        $unitOfMeasureRepository = $this->entityManager->getRepository(UnitOfMeasure::class);
        $unitOfMeasure = $unitOfMeasureRepository->findAll()[0];

        // Envoyer une requête PATCH à l'API pour mettre à jour l'unité de mesure
        $client->request(
            'PATCH',
            "/api/unit_of_measures/{$unitOfMeasure->getId()}",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'testtt',
                'symbol' => 'ttt'
            ])
        );

        // Vérifier que la requête HTTP est bien 200 OK
        $this->assertResponseStatusCodeSame(200);

        // Vérifier que le contenu de la requête est du JSON
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        // Extraire le contenu JSON de la requête
        $responseContent = $client->getResponse()->getContent();
        $data = json_decode($responseContent, true);

        // Vérifier que l'unité de mesure est mise à jour
        $this->assertSame('testtt', $data['name']);
        $this->assertSame('ttt', $data['symbol']);
    }

    public function testUpdateUnitOfMeasureWithInsufficientRole(): void
    {
        // Authentification avec un utilisateur n'ayant pas le rôle SUPER_ADMIN
        $client = $this->createAuthenticatedClient("admin@gmail.com", "password");

        // Récupérer une unité de mesure
        $unitOfMeasureRepository = $this->entityManager->getRepository(UnitOfMeasure::class);
        $unitOfMeasure = $unitOfMeasureRepository->findAll()[0];

        // Envoyer une requête PATCH à l'API pour essayer de mettre à jour l'unité de mesure
        $client->request(
            'PATCH',
            "/api/unit_of_measures/{$unitOfMeasure->getId()}",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Tonne',
                'symbol' => 't'
            ])
        );

        // Vérifier que l'accès est refusé (403 Forbidden)
        $this->assertResponseStatusCodeSame(403);
    }

    public function testDeleteUnitOfMeasureWithAdminRole(): void
    {
        // Authentification avec un utilisateur ayant le rôle SUPER_ADMIN
        $client = $this->createAuthenticatedClient("superadmin@gmail.com", "password");

        // Récupérer une unité de mesure
        $unitOfMeasureRepository = $this->entityManager->getRepository(UnitOfMeasure::class);
        $unitOfMeasure = $unitOfMeasureRepository->findAll()[0];

        // Envoyer une requête DELETE à l'API pour supprimer l'unité de mesure
        $client->request(
            'DELETE',
            "/api/unit_of_measures/{$unitOfMeasure->getId()}"
        );

        // Vérifier que la requête HTTP est bien 204 No Content
        $this->assertResponseStatusCodeSame(204);

        // Vérifier que l'unité de mesure est supprimée
        $deletedUnitOfMeasure = $unitOfMeasureRepository->find($unitOfMeasure->getId());
        $this->assertNull($deletedUnitOfMeasure);
    }

    public function testDeleteUnitOfMeasureWithInsufficientRole(): void
    {
        // Authentification avec un utilisateur n'ayant pas le rôle SUPER_ADMIN
        $client = $this->createAuthenticatedClient("admin@gmail.com", "password");

        // Récupérer une unité de mesure
        $unitOfMeasureRepository = $this->entityManager->getRepository(UnitOfMeasure::class);
        $unitOfMeasure = $unitOfMeasureRepository->findAll()[0];

        // Envoyer une requête DELETE à l'API pour essayer de supprimer l'unité de mesure
        $client->request(
            'DELETE',
            "/api/unit_of_measures/{$unitOfMeasure->getId()}"
        );

        // Vérifier que l'accès est refusé (403 Forbidden)
        $this->assertResponseStatusCodeSame(403);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->databaseTool);
        $this->entityManager->close();
        $this->entityManager = null; // éviter les fuites de mémoire
    }
}
