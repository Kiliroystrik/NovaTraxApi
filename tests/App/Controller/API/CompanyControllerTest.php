<?php

namespace App\Controller\API;

use App\DataFixtures\CompanyFixtures;
use App\Entity\Company;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CompanyControllerTest extends WebTestCase
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
        $this->databaseTool->loadFixtures([
            CompanyFixtures::class,
        ]);
    }

    public function testGetAll(): void
    {
        $this->loadFixtures();

        // Envoyer une requête GET à l'API pour récupérer les entreprises
        $this->client->request('GET', '/api/companies');

        // Vérifier que la réponse HTTP est bien 200 OK
        $this->assertResponseStatusCodeSame(200);

        // Vérifier que le contenu de la réponse est du JSON
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        // Extraire le contenu JSON de la réponse
        $responseContent = $this->client->getResponse()->getContent();
        $data = json_decode($responseContent, true);

        // Extraire les noms des entreprises dans la réponse pour une comparaison plus facile
        $companyNamesInResponse = array_column($data, 'name');
        $companyEmailsInResponse = array_column($data, 'contact_email');

        // Récupérer toutes les entreprises de la base de données
        $repository = $this->entityManager->getRepository(Company::class);
        $expectedCompanies = $repository->findAll();

        // Vérifier que chaque entreprise des fixtures est présente dans la réponse
        foreach ($expectedCompanies as $company) {
            $this->assertContains($company->getName(), $companyNamesInResponse, 'Company name not found in response');
            $this->assertContains($company->getContactEmail(), $companyEmailsInResponse, 'Company email not found in response');
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null; // éviter les fuites de mémoire
    }
}
