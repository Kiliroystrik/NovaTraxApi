<?php

namespace App\Tests\Service;

use App\DataFixtures\CompanyFixtures;
use App\DataFixtures\DriverFixtures;
use App\DataFixtures\VehicleFixtures;
use App\DataFixtures\TourFixtures;
use App\DataFixtures\UnavailabilityFixtures;
use App\Entity\Company;
use App\Entity\Driver;
use App\Entity\Vehicle;
use App\Repository\DriverRepository;
use App\Repository\VehicleRepository;
use App\Service\UnavailabilityService;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UnavailabilityServiceTest extends KernelTestCase
{

    private UnavailabilityService $unavailabilityService;
    private EntityManagerInterface $entityManager;
    private $client;
    private $databaseTool;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();
        $this->unavailabilityService = new UnavailabilityService($this->entityManager->getRepository(Driver::class), $this->entityManager->getRepository(Vehicle::class));

        // Charger les fixtures nécessaires
        $this->databaseTool->loadFixtures([
            CompanyFixtures::class,
            DriverFixtures::class,
            VehicleFixtures::class,
            TourFixtures::class,
            UnavailabilityFixtures::class,
        ]);
    }


    public function testGetAvailableDriversReturnsAvailableDrivers(): void
    {
        // Récupérer une compagnie spécifique depuis les fixtures
        /** @var Company $company */
        $company = $this->entityManager->getRepository(Company::class)->find(1);

        // Définir la plage de dates pour le test
        $startDate = new \DateTimeImmutable('2024-10-10 08:00:00');
        $endDate = new \DateTimeImmutable('2024-10-10 17:00:00');

        // Appeler la méthode du service
        $availableDrivers = $this->unavailabilityService->getAvailableDrivers($startDate, $endDate, $company);

        // Récupérer les conducteurs attendus (en fonction des fixtures)
        $expectedDrivers = $this->entityManager->getRepository(Driver::class)->findAvailableDrivers($startDate, $endDate, $company);

        // Assertions
        $this->assertIsArray($availableDrivers, 'La méthode devrait retourner un tableau.');
        $this->assertCount(count($expectedDrivers), $availableDrivers, 'Le nombre de conducteurs disponibles devrait correspondre.');
        foreach ($expectedDrivers as $driver) {
            $this->assertContains($driver, $availableDrivers, 'Chaque conducteur attendu devrait être disponible.');
        }
    }

    public function testGetAvailableDriversReturnsEmptyArrayWhenNoDriversAvailable(): void
    {
        // Récupérer une compagnie spécifique depuis les fixtures
        /** @var Company $company */
        $company = $this->entityManager->getRepository(Company::class)->find(1);

        // Définir une plage de dates où aucun conducteur n'est disponible
        $startDate = new \DateTimeImmutable('2024-11-01 08:00:00');
        $endDate = new \DateTimeImmutable('2024-11-01 17:00:00');

        // Appeler la méthode du service
        $availableDrivers = $this->unavailabilityService->getAvailableDrivers($startDate, $endDate, $company);

        // Assertions
        $this->assertIsArray($availableDrivers, 'La méthode devrait retourner un tableau.');
        $this->assertCount(0, $availableDrivers, 'Aucun conducteur ne devrait être disponible.');
    }

    public function testGetAvailableVehiclesReturnsAvailableVehicles(): void
    {
        // Récupérer une compagnie spécifique depuis les fixtures
        /** @var Company $company */
        $company = $this->entityManager->getRepository(Company::class)->find(1);

        // Définir la plage de dates pour le test
        $startDate = new \DateTimeImmutable('2024-10-15 08:00:00');
        $endDate = new \DateTimeImmutable('2024-10-15 17:00:00');

        // Appeler la méthode du service
        $availableVehicles = $this->unavailabilityService->getAvailableVehicles($startDate, $endDate, $company);

        // Récupérer les véhicules attendus (en fonction des fixtures)
        $expectedVehicles = $this->entityManager->getRepository(Vehicle::class)->findAvailableVehicles($startDate, $endDate, $company);

        // Assertions
        $this->assertIsArray($availableVehicles, 'La méthode devrait retourner un tableau.');
        $this->assertCount(count($expectedVehicles), $availableVehicles, 'Le nombre de véhicules disponibles devrait correspondre.');
        foreach ($expectedVehicles as $vehicle) {
            $this->assertContains($vehicle, $availableVehicles, 'Chaque véhicule attendu devrait être disponible.');
        }
    }

    public function testGetAvailableVehiclesReturnsEmptyArrayWhenNoVehiclesAvailable(): void
    {
        // Récupérer une compagnie spécifique depuis les fixtures
        /** @var Company $company */
        $company = $this->entityManager->getRepository(Company::class)->find(1);
        // Définir une plage de dates où aucun véhicule n'est disponible
        $startDate = new \DateTimeImmutable('2024-12-01 08:00:00');
        $endDate = new \DateTimeImmutable('2024-12-01 17:00:00');

        // Appeler la méthode du service
        $availableVehicles = $this->unavailabilityService->getAvailableVehicles($startDate, $endDate, $company);

        // Assertions
        $this->assertIsArray($availableVehicles, 'La méthode devrait retourner un tableau.');
        $this->assertCount(0, $availableVehicles, 'Aucun véhicule ne devrait être disponible.');
    }

    public function testGetAvailableDriversWithOverlappingUnavailability(): void
    {
        // Récupérer une compagnie spécifique depuis les fixtures
        /** @var Company $company */
        $company = $this->entityManager->getRepository(Company::class)->find(1);
        // Définir une plage de dates qui chevauchent une indisponibilité
        $startDate = new \DateTimeImmutable('2024-10-20 09:00:00');
        $endDate = new \DateTimeImmutable('2024-10-20 18:00:00');

        // Appeler la méthode du service
        $availableDrivers = $this->unavailabilityService->getAvailableDrivers($startDate, $endDate, $company);

        // Récupérer les conducteurs attendus (excluant ceux avec une indisponibilité)
        $expectedDrivers = $this->entityManager->getRepository(Driver::class)->findAvailableDrivers($startDate, $endDate, $company);

        // Assertions
        $this->assertIsArray($availableDrivers, 'La méthode devrait retourner un tableau.');
        $this->assertCount(count($expectedDrivers), $availableDrivers, 'Le nombre de conducteurs disponibles devrait correspondre.');
        foreach ($expectedDrivers as $driver) {
            $this->assertContains($driver, $availableDrivers, 'Chaque conducteur attendu devrait être disponible.');
        }
    }

    public function testGetAvailableVehiclesWithOverlappingUnavailability(): void
    {
        // Récupérer une compagnie spécifique depuis les fixtures
        /** @var Company $company */
        $company = $this->entityManager->getRepository(Company::class)->find(1);

        // Définir une plage de dates qui chevauchent une indisponibilité
        $startDate = new \DateTimeImmutable('2024-10-25 09:00:00');
        $endDate = new \DateTimeImmutable('2024-10-25 18:00:00');

        // Appeler la méthode du service
        $availableVehicles = $this->unavailabilityService->getAvailableVehicles($startDate, $endDate, $company);

        // Récupérer les véhicules attendus (excluant ceux avec une indisponibilité)
        $expectedVehicles = $this->entityManager->getRepository(Vehicle::class)->findAvailableVehicles($startDate, $endDate, $company);

        // Assertions
        $this->assertIsArray($availableVehicles, 'La méthode devrait retourner un tableau.');
        $this->assertCount(count($expectedVehicles), $availableVehicles, 'Le nombre de véhicules disponibles devrait correspondre.');
        foreach ($expectedVehicles as $vehicle) {
            $this->assertContains($vehicle, $availableVehicles, 'Chaque véhicule attendu devrait être disponible.');
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Fermer l'EntityManager et éviter les fuites de mémoire
        if ($this->entityManager) {
            $this->entityManager->close();
        }
    }
}
