<?php

namespace App\Repository;

use App\Entity\Tour;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TourRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tour::class);
    }

    /**
     * Récupère les IDs des conducteurs assignés à des tournées chevauchantes.
     *
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @return array
     */
    public function findDriverIdsWithOverlappingTours(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        $qb = $this->createQueryBuilder('t')
            ->select('DISTINCT t.driver')
            ->where('t.startDate < :endDate')
            ->andWhere('t.endDate > :startDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        $result = $qb->getQuery()->getScalarResult();

        // Extraire les IDs des conducteurs
        return array_map(fn($row) => $row['driver'], $result);
    }

    /**
     * Récupère les IDs des véhicules assignés à des tournées chevauchantes.
     *
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @return array
     */
    public function findVehicleIdsWithOverlappingTours(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        $qb = $this->createQueryBuilder('t')
            ->select('DISTINCT t.vehicle')
            ->where('t.startDate < :endDate')
            ->andWhere('t.endDate > :startDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        $result = $qb->getQuery()->getScalarResult();

        // Extraire les IDs des véhicules
        return array_map(fn($row) => $row['vehicle'], $result);
    }
}
