<?php

namespace App\Repository;

use App\Entity\Messages;
use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @extends ServiceEntityRepository<Messages>
 */
class MessagesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Messages::class);
    }

    /**
     * Récupère tous les messages échangés entre deux utilisateurs (user1 <-> user2)
     *
     * @param Users $user1
     * @param Users $user2
     * @return Messages[]
     */
    
    public function findMessagesBetweenUsers(Users $user1, Users $user2): array
    {
        return $this->createQueryBuilder('m')
            ->where('(m.user = :user1 AND m.recipient = :user2) OR (m.user = :user2 AND m.recipient = :user1)')
            ->setParameters(new ArrayCollection([
                'user1' => $user1,
                'user2' => $user2
            ]))
            ->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}


//    /**
//     * @return Messages[] Returns an array of Messages objects
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

//    public function findOneBySomeField($value): ?Messages
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

