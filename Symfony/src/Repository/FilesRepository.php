<?php

namespace App\Repository;

use App\Entity\Files;
use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Files>
 */
class FilesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Files::class);
    }
	public function findBySearchTerm(string $term, Users $user): array
	{
		return $this->createQueryBuilder('f')
			->join('f.message', 'm')
			->join('m.channel', 'c')
			->join('c.workspace', 'w')
			->where('LOWER(f.filePath) LIKE :term')
			->andWhere('w.creator = :user')
			->setParameter('term', '%' . $term . '%')
			->setParameter('user', $user)
			->getQuery()
			->getResult();
	}
//    /**
//     * @return Files[] Returns an array of Files objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Files
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
