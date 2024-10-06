<?php

namespace App\Repository;

use App\Entity\Unavailability;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UnavailabilityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Unavailability::class);
    }

    /**
     * Récupère les IDs des conducteurs ayant des indisponibilités chevauchantes.
     *
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @return array
     */
    public function findDriverIdsWithOverlappingUnavailabilities(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        $qb = $this->createQueryBuilder('u')
            ->select('DISTINCT u.driver')
            ->where('u.date BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        $result = $qb->getQuery()->getScalarResult();

        return array_map(fn($row) => $row['driver'], $result);
    }

    /**
     * Récupère les IDs des véhicules ayant des indisponibilités chevauchantes.
     *
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @return array
     */
    public function findVehicleIdsWithOverlappingUnavailabilities(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        $qb = $this->createQueryBuilder('u')
            ->select('DISTINCT u.vehicle')
            ->where('u.date BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        $result = $qb->getQuery()->getScalarResult();

        return array_map(fn($row) => $row['vehicle'], $result);
    }
}
