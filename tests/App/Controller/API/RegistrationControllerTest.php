<?php

namespace App\Controller\API;

use App\DataFixtures\CompanyFixtures;
use App\Entity\Company;
use App\Repository\CompanyRepository;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RegistrationControllerTest extends WebTestCase
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
    }

    private function loadFixtures(): void
    {
        $this->databaseTool->loadFixtures([CompanyFixtures::class]);
    }

    /**
     * Create a client with a default Authorization header.
     *
     * @param string $username
     * @param string $password
     *
     * @return \Symfony\Bundle\FrameworkBundle\Client
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

    /**
     * Test de succès de la création d'une société avec des données valides
     */
    public function testRegisterCompanySuccess(): void
    {
        $data = $this->mockValidCompany();
        $this->createAuthenticatedClient();
        $this->sendRequest($data);

        // Vérifications sur la réponse et en base de données
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $responseData = $this->getResponseData();
        $this->assertValidCompanyResponse($responseData);
        $this->assertCompanyInDatabase($data);
    }

    /**
     * Test avec des données invalides (ex. : nom vide)
     */
    public function testRegisterCompanyInvalidData(): void
    {
        $data = $this->mockInvalidCompany();
        $this->createAuthenticatedClient();
        $this->sendRequest($data);

        // Vérifications de la réponse HTTP 400
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $responseData = $this->getResponseData();
        $this->assertInvalidCompanyResponse($responseData);
    }


    /**
     * Mock des données valides
     */
    private function mockValidCompany(): array
    {
        return [
            'name' => 'Test Company',
            'contactEmail' => 'testcompany@example.com',
            'contactPhone' => '123-456-7890',
        ];
    }

    /**
     * Mock des données invalides (ex. : nom vide)
     */
    private function mockInvalidCompany(): array
    {
        return [
            'name' => '', // Invalide : nom vide
            'contactEmail' => 'testcompany@example.com',
            'contactPhone' => '123-456-7890',
        ];
    }

    /**
     * Envoi d'une requête POST avec les données spécifiées
     */
    private function sendRequest(array $data): void
    {
        $this->client->request(
            'POST',
            '/api/register/company',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
    }

    /**
     * Récupère les données de la réponse et les retourne en tant que tableau
     */
    private function getResponseData(): array
    {
        $responseContent = $this->client->getResponse()->getContent();
        return json_decode($responseContent, true);
    }

    /**
     * Assertions pour vérifier une réponse valide
     */
    private function assertValidCompanyResponse(array $responseData): void
    {
        $this->assertArrayHasKey('id', $responseData);
        $this->assertSame('Test Company', $responseData['name']);
        $this->assertArrayHasKey('createdAt', $responseData);
    }

    /**
     * Vérifie qu'une société est correctement insérée en base de données
     */
    private function assertCompanyInDatabase(array $data): void
    {
        $companyRepository = $this->entityManager->getRepository(Company::class);
        $createdCompany = $companyRepository->findOneBy(['contactEmail' => $data['contactEmail']]);

        $this->assertNotNull($createdCompany);
        $this->assertSame($data['name'], $createdCompany->getName());
        $this->assertSame($data['contactEmail'], $createdCompany->getContactEmail());
        $this->assertSame($data['contactPhone'], $createdCompany->getContactPhone());
        $this->assertInstanceOf(\DateTimeImmutable::class, $createdCompany->getCreatedAt());
    }

    /**
     * Assertions pour vérifier une réponse invalide
     */
    private function assertInvalidCompanyResponse(array $responseData): void
    {
        $this->assertArrayHasKey('error', $responseData);
        $this->assertStringContainsString('Le nom de la société ne doit pas être vide.', $responseData['error']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null; // éviter les fuites de mémoire
    }
}
