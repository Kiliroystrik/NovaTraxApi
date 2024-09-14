<?php

namespace App\Unit\Tests;

use App\DataFixtures\CompanyFixtures;
use App\DataFixtures\UserFixtures;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiTokenTest extends WebTestCase
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

        // Charger les fixtures avant chaque test
        $this->databaseTool->loadFixtures([
            CompanyFixtures::class,  // Si nécessaire
            UserFixtures::class      // Charger la fixture utilisateur
        ]);
    }

    /**
     * Récupérer un token JWT en fonction des identifiants fournis.
     *
     * @param string $username
     * @param string $password
     * @return string|null Le token JWT ou null si l'authentification échoue.
     */
    private function getToken(string $username = 'superadmin@gmail.com', string $password = 'password'): ?string
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

        $response = $this->client->getResponse();
        $statusCode = $response->getStatusCode();

        // Vérifier que l'authentification réussit (code HTTP 200)
        if ($statusCode !== 200) {
            return null;
        }

        $data = json_decode($response->getContent(), true);

        // Vérification que le token est présent dans la réponse
        if (!isset($data['token'])) {
            return null;
        }

        return $data['token'];
    }

    /**
     * Créer un client authentifié avec un jeton JWT dans l'en-tête Authorization.
     *
     * @param string $username
     * @param string $password
     * @return \Symfony\Bundle\FrameworkBundle\KernelBrowser|null
     */
    protected function createAuthenticatedClient(string $username = 'superadmin@gmail.com', string $password = 'password'): ?\Symfony\Bundle\FrameworkBundle\KernelBrowser
    {
        $token = $this->getToken($username, $password);

        if ($token === null) {
            $this->fail('Authentication failed. No token received.');
        }

        // Ajout du jeton dans les en-têtes pour les requêtes futures
        $this->client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $token));

        return $this->client;
    }

    public function testAuthentication(): void
    {
        // Authentification réussie et récupération du token
        $this->createAuthenticatedClient();

        // Requête GET sur une route protégée après authentification
        $this->client->request('GET', '/api/companies');

        // Vérifier que la réponse est 200 (la requête a été autorisée)
        $this->assertResponseStatusCodeSame(200, 'Échec de l\'accès à l\'API protégée avec un token valide.');
    }

    public function testAuthenticationWithInvalidCredentials(): void
    {
        // Tentative d'authentification avec des identifiants invalides
        $token = $this->getToken('invalid_username', 'invalid_password');

        // Vérifier que la récupération du token a échoué
        $this->assertNull($token, 'Authentication should fail with invalid credentials.');

        // Vérifier que la requête HTTP est 401 (non autorisée)
        $this->assertResponseStatusCodeSame(401, 'Authentication with invalid credentials should return 401.');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null; // éviter les fuites de mémoire
    }
}
