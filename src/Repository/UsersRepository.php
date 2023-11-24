<?php

namespace App\Repository;

use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PhpParser\Node\Expr\Cast\Array_;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Users>
* @implements PasswordUpgraderInterface<Users>
 *
 * @method Users|null find($id, $lockMode = null, $lockVersion = null)
 * @method Users|null findOneBy(array $criteria, array $orderBy = null)
 * @method Users[]    findAll()
 * @method Users[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsersRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Users::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Users) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findAllByOrganization(int $organizationId): array
    {
        
        return $this->createQueryBuilder('u')
            ->join('u.relations', 'r')
            ->andWhere('r.organization = :val')
            ->setParameter('val', $organizationId)
            ->getQuery()
            ->getResult()
        ;
    }
    
            // faire une jointure sur relation JOIN id_user
            // prendre les relation.organization_id = $orgnizationId
        
            // table A [id_user, nom]             table B [id_user, adresse]
            // 1 / thay                           1 / rue
            // 2 / sim                            2 / ville
            // 3 / may                            4 / bourg 
            
        
            // SELECT A.id_user, A.nom, B.adresse FROM A 
            // JOIN B ON B.id_user = A.id_user;
            // [id_user, nom, adresse]
            // 1 / thay / rue
            // 2 / sim / ville
    
            // SELECT A.id_user, A.nom, B.adresse FROM A 
            // LEFT JOIN B ON B.id_user = A.id_user;
            // [id_user, nom, adresse]
            // 1 / thay / rue
            // 2 / sim / ville
            // 3 / may / NULL
    
            // SELECT A.id_user, A.nom, B.adresse FROM A 
            // RIGHT JOIN B ON B.id_user = A.id_user;
            // [id_user, nom, adresse]
            // 1 / thay / rue
            // 2 / sim / ville
            // 4 / NULL / bourg
    

//    /**
//     * @return Users[] Returns an array of Users objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Users
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
