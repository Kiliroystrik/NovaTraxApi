<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\Vehicle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vehicle>
 */
class VehicleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehicle::class);
    }

    //    /**
    //     * @return Vehicle[] Returns an array of Vehicle objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->clientOrderBy('v.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Vehicle
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * Récupère les véhicules disponibles pour une plage de dates donnée.
     *
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @param Company $company
     * @return Vehicle[]
     */
    public function findAvailableVehicles(\DateTimeInterface $startDate, \DateTimeInterface $endDate, Company $company): array
    {
        $qb = $this->createQueryBuilder('v')
            ->leftJoin(
                'v.tours',
                't',
                Join::WITH,
                't.startDate < :endDate AND t.endDate > :startDate'
            )
            ->leftJoin(
                'v.unavailabilities',
                'u',
                Join::WITH,
                'u.startDate < :endDate AND u.endDate > :startDate'
            )
            ->where('v.company = :company')
            ->andWhere('t.id IS NULL')
            ->andWhere('u.id IS NULL')
            ->setParameter('company', $company)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        return $qb->getQuery()->getResult();
    }
}
