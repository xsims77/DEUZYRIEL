<?php

namespace App\Repository;

use App\Entity\MoralCustomers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MoralCustomers>
 *
 * @method MoralCustomers|null find($id, $lockMode = null, $lockVersion = null)
 * @method MoralCustomers|null findOneBy(array $criteria, array $orderBy = null)
 * @method MoralCustomers[]    findAll()
 * @method MoralCustomers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MoralCustomersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MoralCustomers::class);
    }

//    /**
//     * @return MoralCustomers[] Returns an array of MoralCustomers objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?MoralCustomers
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
