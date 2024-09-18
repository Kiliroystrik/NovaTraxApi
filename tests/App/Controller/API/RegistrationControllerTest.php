<?php

namespace App\Controller\API;

use App\DataFixtures\CompanyFixtures;
use App\DataFixtures\UserFixtures;
use App\DTO\RegistrationDTO;
use App\Entity\Company;
use App\Entity\User;
use App\Repository\CompanyRepository;
use App\Service\CompanyRegistrationService;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationControllerTest extends WebTestCase
{
    private $entityManager;
    private $client;
    private $databaseTool;
    private $validator;
    private $companyRegistrationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
        $this->companyRegistrationService = static::getContainer()->get(CompanyRegistrationService::class);

        $this->loadFixtures();
    }

    private function loadFixtures(): void
    {
        $this->databaseTool->loadFixtures([
            CompanyFixtures::class,
            UserFixtures::class
        ]);
    }

    /**
     * Test de succès de la création d'une entreprise avec des données valides.
     */
    public function testRegisterCompanySuccess(): void
    {
        $data = $this->mockValidCompany();
        $this->sendRequest($data);

        $responseData = $this->getResponseData();
        // Vérifications sur la réponse et en base de données
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertValidCompanyResponse($responseData);
        $this->assertCompanyInDatabase($data);
        $this->assertUserInDatabase($data);
    }

    /**
     * Test avec des données invalides (ex. : nom vide).
     */
    public function testRegisterCompanyInvalidData(): void
    {
        $data = $this->mockInvalidCompany();
        $this->sendRequest($data);

        // Vérifications de la réponse HTTP 400
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $responseData = $this->getResponseData();
        $this->assertInvalidCompanyResponse($responseData);
    }

    /**
     * Mock des données valides pour créer une entreprise.
     */
    private function mockValidCompany(): array
    {
        return [
            'companyName' => 'Test Company',
            'contactEmail' => 'testcompany@example.com',
            'contactPhone' => '123-456-7890',
            'userPassword' => 'password',
            'userFirstName' => 'John',
            'userLastName' => 'Doe',
            'userEmail' => 'testcompany@example.com',
        ];
    }

    /**
     * Mock des données invalides (ex. : nom vide).
     */
    private function mockInvalidCompany(): array
    {
        return [
            'companyName' => '', // Invalide : nom vide
            'contactEmail' => 'testcompany@example.com',
            'contactPhone' => '123-456-7890',
            'userPassword' => 'password',
            'userFirstName' => 'John',
            'userLastName' => 'Doe',
            'userEmail' => 'testcompany@example.com',
        ];
    }

    /**
     * Envoi d'une requête POST avec les données spécifiées.
     */
    private function sendRequest(array $data): void
    {
        $this->client->request(
            'POST',
            '/api/register_company',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
    }

    /**
     * Récupère les données de la réponse et les retourne en tant que tableau.
     */
    private function getResponseData(): array
    {
        $responseContent = $this->client->getResponse()->getContent();
        return json_decode($responseContent, true);
    }

    /**
     * Vérifie qu'une entreprise est correctement insérée en base de données.
     */
    private function assertCompanyInDatabase(array $data): void
    {
        /** @var CompanyRepository $companyRepository */
        $companyRepository = $this->entityManager->getRepository(Company::class);

        // Cherche la société par son email unique
        $createdCompany = $companyRepository->findOneBy(['contactEmail' => $data['contactEmail']]);

        // Ajoute cette vérification pour savoir si la société est récupérée
        if (!$createdCompany) {
            throw new \Exception('Company not found in the database.');
        }

        // Vérifie que les données en base correspondent à celles envoyées
        $this->assertNotNull($createdCompany);
        $this->assertSame($data['companyName'], $createdCompany->getName());
        $this->assertSame($data['contactEmail'], $createdCompany->getContactEmail());
        $this->assertSame($data['contactPhone'], $createdCompany->getContactPhone());
        $this->assertInstanceOf(\DateTimeImmutable::class, $createdCompany->getCreatedAt());
    }


    /**
     * Vérifie qu'un utilisateur est correctement inséré en base de données.
     */
    private function assertUserInDatabase(array $data): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);
        $createdUser = $userRepository->findOneBy(['email' => $data['userEmail']]);

        $this->assertNotNull($createdUser);
        $this->assertSame($data['userEmail'], $createdUser->getEmail());
        $this->assertSame($data['userFirstName'], $createdUser->getFirstName());
        $this->assertSame($data['userLastName'], $createdUser->getLastName());
        $this->assertInstanceOf(\DateTimeImmutable::class, $createdUser->getCreatedAt());
    }

    /**
     * Assertions pour vérifier une réponse valide.
     */
    private function assertValidCompanyResponse(array $responseData): void
    {
        $this->assertArrayHasKey('id', $responseData);
        $this->assertSame('Test Company', $responseData['name']);
        $this->assertArrayHasKey('createdAt', $responseData);
    }

    /**
     * Assertions pour vérifier une réponse invalide.
     */
    private function assertInvalidCompanyResponse(array $responseData): void
    {
        $this->assertArrayHasKey('error', $responseData);
        $this->assertStringContainsString('Le nom de l\'entreprise ne doit pas être vide.', $responseData['error']);
    }


    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->databaseTool);
        $this->entityManager->close();
        $this->entityManager = null; // éviter les fuites de mémoire
    }
}
