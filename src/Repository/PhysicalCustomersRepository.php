<?php

namespace App\Repository;

use App\Entity\PhysicalCustomers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PhysicalCustomers>
 *
 * @method PhysicalCustomers|null find($id, $lockMode = null, $lockVersion = null)
 * @method PhysicalCustomers|null findOneBy(array $criteria, array $orderBy = null)
 * @method PhysicalCustomers[]    findAll()
 * @method PhysicalCustomers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PhysicalCustomersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PhysicalCustomers::class);
    }

//    /**
//     * @return PhysicalCustomers[] Returns an array of PhysicalCustomers objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?PhysicalCustomers
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
