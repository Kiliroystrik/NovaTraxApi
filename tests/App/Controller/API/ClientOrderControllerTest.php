<?php

namespace App\Controller\API;

use App\DataFixtures\ClientFixtures;
use App\DataFixtures\ClientOrderFixtures;
use App\DataFixtures\CompanyFixtures;
use App\DataFixtures\UserFixtures;
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
        $this->client->request('GET', '/api/client/orders');

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

    // public function getCompany(): void
    // {
    //     $client = $this->createAuthenticatedClient();

    //     $user = "superadmin@gmail.com";

    //     $repository = $this->entityManager->getRepository(User::class);

    //     $user = $repository->findOneBy(['email' => $user]);

    //     // Vérifier que l'utilisateur est bien récupéré et associé à une entreprise
    //     $this->assertNotNull($user, 'L\'utilisateur n\'a pas été trouvé en base de données.');
    //     $userCompany = $user->getCompany();
    //     $this->assertNotNull($userCompany, 'L\'utilisateur n\'est pas associé à une entreprise.');

    //     // Envoyer une requête GET à l'API pour récupérer l'entreprise de l'utilisateur
    //     $client->request('GET', '/api/company');

    //     // Vérifier que la réponse HTTP est bien 200 OK
    //     $this->assertResponseStatusCodeSame(200);

    //     // Extraire la réponse JSON
    //     $responseContent = $client->getResponse()->getContent();
    //     $data = json_decode($responseContent, true);

    //     // Vérifier que l'API renvoie bien l'entreprise associée à l'utilisateur
    //     $this->assertSame($userCompany->getName(), $data['name'], 'Le nom de l\'entreprise ne correspond pas.');
    //     $this->assertSame($userCompany->getContactEmail(), $data['contactEmail'], 'L\'email de contact de l\'entreprise ne correspond pas.');
    // }

    // public function testUpdateCompany(): void
    // {
    //     $client = $this->createAuthenticatedClient();

    //     // Récupérer l'utilisateur actuel
    //     $userRepository = $this->entityManager->getRepository(User::class);
    //     $user = $userRepository->findOneBy(['email' => 'superadmin@gmail.com']);

    //     // Vérifier que l'utilisateur et la compagnie associée existent
    //     $this->assertNotNull($user, 'L\'utilisateur n\'a pas été trouvé en base de données.');
    //     $userCompany = $user->getCompany();
    //     $this->assertNotNull($userCompany, 'L\'utilisateur n\'est pas associé à une entreprise.');

    //     // Données à mettre à jour via PATCH
    //     $updatedData = [
    //         'contactEmail' => 'new-email@example.com',
    //         'contactPhone' => '987-654-3210'
    //     ];

    //     // Envoyer une requête PATCH à l'API pour mettre à jour l'entreprise de l'utilisateur
    //     $client->request(
    //         'PATCH',
    //         '/api/company',
    //         [],
    //         [],
    //         ['CONTENT_TYPE' => 'application/json'],
    //         json_encode($updatedData)
    //     );

    //     // Vérifier que la réponse HTTP est bien 200 OK
    //     $this->assertResponseStatusCodeSame(204);

    //     // Vérifier que la mise à jour est bien reflétée en base de données
    //     $updatedCompany = $userRepository->findOneBy(['email' => 'superadmin@gmail.com'])->getCompany();
    //     $this->assertSame('new-email@example.com', $updatedCompany->getContactEmail(), 'L\'email de contact de l\'entreprise n\'a pas été mis à jour en base de données.');
    //     $this->assertSame('987-654-3210', $updatedCompany->getContactPhone(), 'Le numéro de téléphone de l\'entreprise n\'a pas été mis à jour en base de données.');
    // }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->databaseTool);
        $this->entityManager->close();
        $this->entityManager = null; // éviter les fuites de mémoire
    }
}
