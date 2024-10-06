<?php

namespace App\Service;

use App\Repository\DriverRepository;
use App\Repository\VehicleRepository;
use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;

class UnavailabilityService
{
    private DriverRepository $driverRepository;
    private VehicleRepository $vehicleRepository;

    public function __construct(
        DriverRepository $driverRepository,
        VehicleRepository $vehicleRepository
    ) {
        $this->driverRepository = $driverRepository;
        $this->vehicleRepository = $vehicleRepository;
    }

    /**
     * Récupère les conducteurs disponibles pour une plage de dates donnée.
     *
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @param Company $company
     * @return array
     */
    public function getAvailableDrivers(\DateTimeInterface $startDate, \DateTimeInterface $endDate, Company $company): array
    {
        return $this->driverRepository->findAvailableDrivers($startDate, $endDate, $company);
    }

    /**
     * Récupère les véhicules disponibles pour une plage de dates donnée.
     *
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @param Company $company
     * @return array
     */
    public function getAvailableVehicles(\DateTimeInterface $startDate, \DateTimeInterface $endDate, Company $company): array
    {
        return $this->vehicleRepository->findAvailableVehicles($startDate, $endDate, $company);
    }
}
