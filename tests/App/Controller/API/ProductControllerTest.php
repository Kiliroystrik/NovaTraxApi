<?php

namespace App\Controller\API;

use App\DataFixtures\ClientFixtures;
use App\DataFixtures\ProductFixtures;
use App\DataFixtures\CompanyFixtures;
use App\DataFixtures\UnitOfMeasureFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Product;
use App\Entity\UnitOfMeasure;
use App\Entity\User;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductControllerTest extends WebTestCase
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
            ClientFixtures::class,
            UnitOfMeasureFixtures::class,
            ProductFixtures::class
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

    public function testGetProducts(): void
    {
        // Authentification
        // $this->createAuthenticatedClient("admin@gmail.com", "password");
        $this->createAuthenticatedClient();

        // Envoyer une requête GET à l'API pour récupérer les entreprises
        $this->client->request('GET', '/api/products');

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
        $products = $data['items'];

        // Je récupère la quantité totale des commandes des clients
        $totalProducts = $data['totalItems'];

        // Je récupère les numéros des commandes des clients
        // $productsNumbers = array_column($products, 'productNumber');

        // Récupérer toutes les entreprises de la base de données
        $repository = $this->entityManager->getRepository(Product::class);
        $expectedProducts = $repository->findAll();

        // Vérifier que la quantité totale des commandes des clients est correcte
        $this->assertCount($totalProducts, $expectedProducts);

        // Vérifier que chaque entreprise des fixtures est présente dans la réponse
        // foreach ($expectedProducts as $product) {
        //     $this->assertContains($product->getProductNumber(), $productsNumbers, 'Product number not found in response');
        // }
    }

    public function testGetProduct(): void
    {
        // Authentification
        $client = $this->createAuthenticatedClient();

        // Récupérer un produit de mon utilisateur
        $productRepository = $this->entityManager->getRepository(Product::class);
        $product = $productRepository->findAll()[0];

        // Envoyer une requête GET à l'API pour récupérer le produit
        $client->request('GET', "/api/products/{$product->getId()}");

        // Vérifier que la réponse HTTP est bien 200 OK
        $this->assertResponseStatusCodeSame(200);

        // Vérifier que le contenu de la réponse est du JSON
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        // Extraire le contenu JSON de la réponse
        $responseContent = $client->getResponse()->getContent();
        $data = json_decode($responseContent, true);

        // Vérifier que la réponse contient les informations de le produit
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('description', $data);
    }

    public function testCreateProduct(): void
    {
        // Authentification avec un utilisateur spécifique
        $client = $this->createAuthenticatedClient("admin@gmail.com", "password");

        // Récupérer un utilisateur pour vérifier l'association avec le produit
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'admin@gmail.com']);

        // Récupérer la compagnie de l'utilisateur
        $userCompany = $user->getCompany();

        $unitOfMeasureRepository = $this->entityManager->getRepository(UnitOfMeasure::class);
        $unitOfMeasure = $unitOfMeasureRepository->findAll()[0];

        // Envoyer une requête POST à l'API pour ajouter un produit
        $client->request(
            'POST',
            '/api/products',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Product 1',
                'description' => 'Product 1 description',
                'unitOfMeasure' => $unitOfMeasure->getId(),
            ])
        );

        // Vérifier que la requête HTTP est bien 201 Created
        $this->assertResponseStatusCodeSame(201);

        // Vérifier que le contenu de la réponse est du JSON
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        // Extraire le contenu JSON de la requête
        $responseContent = $client->getResponse()->getContent();
        $data = json_decode($responseContent, true);

        // Vérifier que la réponse contient les informations de le produit
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data); // Vérifier que l'productNumber est généré
        $this->assertArrayHasKey('description', $data);
        $this->assertArrayHasKey('unitOfMeasure', $data);

        // Vérifier que le produit est associé à la compagnie de l'utilisateur
        $this->assertSame($userCompany->getName(), $data['company']['name']);
    }


    public function testUpdateProduct(): void
    {
        // Authentification
        $client = $this->createAuthenticatedClient("admin@gmail.com", "password");

        // Récupérer un produit de mon utilisateur
        $productRepository = $this->entityManager->getRepository(Product::class);
        $product = $productRepository->findAll()[0];

        // Récupérer une unité de mesure
        $unitOfMeasureRepository = $this->entityManager->getRepository(UnitOfMeasure::class);
        $unitOfMeasure = $unitOfMeasureRepository->findAll()[1];

        // Envoyer une requête PUT ou PATCH à l'API pour mettre à jour le produit
        $client->request(
            'PATCH',
            "/api/products/{$product->getId()}",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'produit modifié',
                'description' => 'produit modifié',
                'unitOfMeasure' => $unitOfMeasure->getId(),
            ])
        );

        // Vérifier que la requête HTTP est bien 200 OK
        $this->assertResponseStatusCodeSame(200);

        // Vérifier que le contenu de la requête est du JSON
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        // Extraire le contenu JSON de la requête
        $responseContent = $client->getResponse()->getContent();
        $data = json_decode($responseContent, true);

        // Vérifier que le produit est mise à jour
        $this->assertSame('produit modifié', $data['name']);
        $this->assertSame('produit modifié', $data['description']);
        $this->assertSame($unitOfMeasure->getId(), $data['unitOfMeasure']['id']);
    }


    public function testDeleteProduct(): void
    {
        // Authentification
        $client = $this->createAuthenticatedClient("admin@gmail.com", "password");

        // Récupérer un produit de mon utilisateur
        $productRepository = $this->entityManager->getRepository(Product::class);
        $product = $productRepository->findAll()[0];

        // Envoyer une requête DELETE à l'API pour supprimer le produit
        $client->request(
            'DELETE',
            "/api/products/{$product->getId()}"
        );

        // Vérifier que la requête HTTP est bien 204 No Content
        $this->assertResponseStatusCodeSame(204);

        // Récupérer à nouveau le produit pour vérifier qu'il est supprimé
        $deletedProduct = $productRepository->find($product->getId());
        $this->assertNull($deletedProduct);
    }


    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->databaseTool);
        $this->entityManager->close();
        $this->entityManager = null; // éviter les fuites de mémoire
    }
}
