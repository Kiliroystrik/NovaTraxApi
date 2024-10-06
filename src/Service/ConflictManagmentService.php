<?php

namespace App\Service;

use App\Repository\UnavailabilityRepository;
use App\Repository\TourRepository;

class ConflictManagementService
{
    private UnavailabilityRepository $unavailabilityRepository;
    private TourRepository $tourRepository;

    public function __construct(UnavailabilityRepository $unavailabilityRepository, TourRepository $tourRepository)
    {
        $this->unavailabilityRepository = $unavailabilityRepository;
        $this->tourRepository = $tourRepository;
    }

    /**
     * Vérifie les conflits lors de la création d'une indisponibilité.
     *
     * @param int $driverId
     * @param int $vehicleId
     * @param \DateTimeImmutable $startDate
     * @param \DateTimeImmutable $endDate
     * @return array ['driverConflict' => bool, 'vehicleConflict' => bool]
     */
    public function checkConflictsForUnavailability(int $driverId, int $vehicleId, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        $driverConflict = $this->tourRepository->isDriverInOverlappingTour($driverId, $startDate, $endDate);
        $vehicleConflict = $this->tourRepository->isVehicleInOverlappingTour($vehicleId, $startDate, $endDate);

        return [
            'driverConflict' => $driverConflict,
            'vehicleConflict' => $vehicleConflict,
        ];
    }
}
