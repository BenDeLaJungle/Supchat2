<?php

namespace App\Repository;

use App\Entity\Users;
use App\Entity\Channels;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Channels>
 */
class ChannelsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Channels::class);
    }
	
	public function findBySearchTerm(string $term, Users $user): array
	{
		$qb = $this->createQueryBuilder('c');

		$qb
			->join('c.workspace', 'w')
			->join('App\Entity\WorkspaceMembers', 'wm', 'WITH', 'wm.workspace = w AND wm.user = :user')
			->join('wm.role', 'r')
			->where($qb->expr()->like('LOWER(c.name)', ':term'))
			->andWhere(
				$qb->expr()->orX(
					'r.id = 3', // Admin
					$qb->expr()->andX('r.id = 2', 'c.minRole <= 2'),
					$qb->expr()->andX('r.id = 1', 'c.minRole = 1'),
					$qb->expr()->exists(
						'SELECT 1 FROM App\Entity\Messages m2 WHERE m2.channel = c AND m2.user = :user'
					)
				)
			)
			->setParameter('term', '%' . strtolower($term) . '%')
			->setParameter('user', $user);

		return $qb->getQuery()->getResult();
	}


//    /**
//     * @return Channels[] Returns an array of Channels objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Channels
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
