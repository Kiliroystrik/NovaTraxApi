<?php

namespace App\Controller\API;

use App\DataFixtures\ClientFixtures;
use App\DataFixtures\ClientOrderFixtures;
use App\DataFixtures\CompanyFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Client;
use App\Entity\ClientOrder;
use App\Entity\Company;
use App\Entity\User;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ClientOrderControllerTest extends WebTestCase
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
            ClientFixtures::class,
            ClientOrderFixtures::class
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

    public function testGetClientOrders(): void
    {
        // Authentification
        // $this->createAuthenticatedClient("admin@gmail.com", "password");
        $this->createAuthenticatedClient();

        // Envoyer une requête GET à l'API pour récupérer les entreprises
        $this->client->request('GET', '/api/orders');

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
        $clientOrders = $data['items'];

        // Je récupère la quantité totale des commandes des clients
        $totalClientOrders = $data['totalItems'];

        // Je récupère les numéros des commandes des clients
        // $clientOrdersNumbers = array_column($clientOrders, 'orderNumber');

        // Récupérer toutes les entreprises de la base de données
        $repository = $this->entityManager->getRepository(ClientOrder::class);
        $expectedClientOrders = $repository->findAll();

        // Vérifier que la quantité totale des commandes des clients est correcte
        $this->assertCount($totalClientOrders, $expectedClientOrders);

        // Vérifier que chaque entreprise des fixtures est présente dans la réponse
        // foreach ($expectedClientOrders as $clientOrder) {
        //     $this->assertContains($clientOrder->getOrderNumber(), $clientOrdersNumbers, 'Order number not found in response');
        // }
    }

    public function testGetClientOrder(): void
    {
        // Authentification
        $client = $this->createAuthenticatedClient();

        // Récupérer une commande de mon utilisateur
        $clientOrderRepository = $this->entityManager->getRepository(ClientOrder::class);
        $clientOrder = $clientOrderRepository->findAll()[0];

        // Envoyer une requête GET à l'API pour récupérer la commande
        $client->request('GET', "/api/orders/{$clientOrder->getId()}");

        // Vérifier que la réponse HTTP est bien 200 OK
        $this->assertResponseStatusCodeSame(200);

        // Vérifier que le contenu de la réponse est du JSON
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        // Extraire le contenu JSON de la réponse
        $responseContent = $client->getResponse()->getContent();
        $data = json_decode($responseContent, true);

        // Vérifier que la réponse contient les informations de la commande
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('orderNumber', $data);
    }

    public function testCreateClientOrder(): void
    {
        // Authentification avec un utilisateur spécifique
        $client = $this->createAuthenticatedClient("admin@gmail.com", "password");

        // Récupérer un utilisateur pour vérifier l'association avec la commande
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'admin@gmail.com']);

        // Récupérer un client pour l'associer à la commande (relation ManyToOne)
        $clientRepository = $this->entityManager->getRepository(Client::class);
        $clientEntity = $clientRepository->findOneBy([]);

        // Récupérer la compagnie de l'utilisateur
        $userCompany = $user->getCompany();

        // Envoyer une requête POST à l'API pour ajouter une commande
        $client->request(
            'POST',
            '/api/orders',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'expectedDeliveryDate' => '2024-01-01',
                'client' => $clientEntity->getId(), // On passe l'ID du client
            ])
        );

        // Vérifier que la requête HTTP est bien 201 Created
        $this->assertResponseStatusCodeSame(201);

        // Vérifier que le contenu de la réponse est du JSON
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        // Extraire le contenu JSON de la requête
        $responseContent = $client->getResponse()->getContent();
        $data = json_decode($responseContent, true);

        // Vérifier que la réponse contient les informations de la commande
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('orderNumber', $data); // Vérifier que l'orderNumber est généré
        $this->assertArrayHasKey('company', $data);
        $this->assertArrayHasKey('client', $data);

        // Vérifier que la commande est associée à la compagnie de l'utilisateur
        $this->assertSame($userCompany->getName(), $data['company']['name']);

        // Vérifier que la commande est associée au bon client
        $this->assertSame($clientEntity->getId(), $data['client']['id']);
    }


    public function testUpdateClientOrder(): void
    {
        // Authentification
        $client = $this->createAuthenticatedClient("admin@gmail.com", "password");

        // Récupérer une commande de mon utilisateur
        $clientOrderRepository = $this->entityManager->getRepository(ClientOrder::class);
        $clientOrder = $clientOrderRepository->findAll()[0];

        // Envoyer une requête PUT ou PATCH à l'API pour mettre à jour la commande
        $client->request(
            'PATCH',
            "/api/orders/{$clientOrder->getId()}",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'expectedDeliveryDate' => '2024-01-01',
            ])
        );

        // Vérifier que la requête HTTP est bien 200 OK
        $this->assertResponseStatusCodeSame(200);

        // Vérifier que le contenu de la requête est du JSON
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        // Extraire le contenu JSON de la requête
        $responseContent = $client->getResponse()->getContent();
        $data = json_decode($responseContent, true);

        // Vérifier que la commande est mise à jour
        // Je passe ma date en datetimeimmutable pour la comparer à l'objet de la requête
        $expectedDeliveryDate = new \DateTimeImmutable('2024-01-01');
        $dataExpectedDeliveryDate = new \DateTimeImmutable($data['expectedDeliveryDate']);
        $this->assertEquals($expectedDeliveryDate, $dataExpectedDeliveryDate);
    }


    public function testDeleteClientOrder(): void
    {
        // Authentification
        $client = $this->createAuthenticatedClient("admin@gmail.com", "password");

        // Récupérer une commande de mon utilisateur
        $clientOrderRepository = $this->entityManager->getRepository(ClientOrder::class);
        $clientOrder = $clientOrderRepository->findAll()[0];

        // Envoyer une requête DELETE à l'API pour supprimer la commande
        $client->request(
            'DELETE',
            "/api/orders/{$clientOrder->getId()}"
        );

        // Vérifier que la requête HTTP est bien 204 No Content
        $this->assertResponseStatusCodeSame(204);

        // Vérifier que la commande est supprimée
        $clientOrderRepository = $this->entityManager->getRepository(ClientOrder::class);
        $clientOrder = $clientOrderRepository->find($clientOrder->getId());
        $this->assertNull($clientOrder);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->databaseTool);
        $this->entityManager->close();
        $this->entityManager = null; // éviter les fuites de mémoire
    }
}
