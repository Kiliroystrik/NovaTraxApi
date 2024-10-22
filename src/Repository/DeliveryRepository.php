<?php

namespace App\Repository;

use App\Entity\Delivery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Delivery>
 */
class DeliveryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Delivery::class);
    }

    //    /**
    //     * @return Delivery[] Returns an array of Delivery objects
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

    //    public function findOneBySomeField($value): ?Delivery
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * Crée un QueryBuilder pour les livraisons en fonction des critères.
     *
     * @param array $criteria ['expectedDeliveryDate' => ['from' => DateTime, 'to' => DateTime]]
     * @return QueryBuilder
     */
    public function getQueryByCriteria(array $criteria = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('d')
            ->leftJoin('d.tour', 't')
            ->leftJoin('d.status', 's')
            ->addSelect('t', 's')
            ->orderBy('d.expectedDeliveryDate', 'ASC');

        // Filtrer par date si fourni
        if (isset($criteria['expectedDeliveryDate'])) {
            $qb->andWhere('d.expectedDeliveryDate BETWEEN :start AND :end')
                ->setParameter('start', $criteria['expectedDeliveryDate']['from'])
                ->setParameter('end', $criteria['expectedDeliveryDate']['to']);
        }

        // Ajouter d'autres filtres si nécessaire

        return $qb;
    }
}
