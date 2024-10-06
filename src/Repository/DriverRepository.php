<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\Driver;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Driver>
 */
class DriverRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Driver::class);
    }

    //    /**
    //     * @return Driver[] Returns an array of Driver objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->clientOrderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Driver
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }


    /**
     * Récupère les conducteurs disponibles pour une plage de dates donnée.
     *
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @param Company $company
     * @return Driver[]
     */
    public function findAvailableDrivers(\DateTimeInterface $startDate, \DateTimeInterface $endDate, Company $company): array
    {
        $qb = $this->createQueryBuilder('d')
            ->leftJoin(
                'd.tours',
                't',
                Join::WITH,
                't.startDate < :endDate AND t.endDate > :startDate'
            )
            ->leftJoin(
                'd.unavailabilities',
                'u',
                Join::WITH,
                'u.startDate < :endDate AND u.endDate > :startDate'
            )
            ->where('d.company = :company')
            ->andWhere('t.id IS NULL')
            ->andWhere('u.id IS NULL')
            ->setParameter('company', $company)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        return $qb->getQuery()->getResult();
    }
}
