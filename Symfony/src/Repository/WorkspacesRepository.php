<?php

namespace App\Repository;

use App\Entity\Workspaces;
use App\Entity\WorkspaceMembers;
use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Workspaces>
 */
class WorkspacesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Workspaces::class);
    }

    /**
     * Retourne les workspaces dont l'utilisateur est membre
     * @param Users $user
     * @return Workspaces[]
     */
    public function findByUser(Users $user): array
    {
        return $this->createQueryBuilder('w')
            ->innerJoin(
                WorkspaceMembers::class,
                'm',
                'WITH',
                'm.workspace = w'
            )
            ->andWhere('m.user = :user')
            ->setParameter('user', $user)
            ->orderBy('w.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
	public function findBySearchTerm(string $term, Users $user): array
	{
		return $this->createQueryBuilder('w')
			->join(WorkspaceMembers::class, 'wm', 'WITH', 'wm.workspace = w')
			->where('wm.user = :user')
			->andWhere('LOWER(w.name) LIKE :term')
			->setParameter('user', $user)
			->setParameter('term', '%' . strtolower($term) . '%')
			->getQuery()
			->getResult();
	}
    public function findPublicNotJoinedByUser(Users $user): array
    {
        $qb = $this->createQueryBuilder('w')
            ->leftJoin('w.workspaceMembers', 'wm', 'WITH', 'wm.user = :user')
            ->where('w.status = 1') // 1 = public
            ->andWhere('wm.id IS NULL') // non membre
            ->setParameter('user', $user);

        return $qb->getQuery()->getResult();
    }


    // /**
    //  * @return Workspaces[] Returns an array of Workspaces objects
    //  */
    // public function findByExampleField($value): array
    // {
    //     return $this->createQueryBuilder('w')
    //         ->andWhere('w.exampleField = :val')
    //         ->setParameter('val', $value)
    //         ->orderBy('w.id', 'ASC')
    //         ->setMaxResults(10)
    //         ->getQuery()
    //         ->getResult();
    // }

    // public function findOneBySomeField($value): ?Workspaces
    // {
    //     return $this->createQueryBuilder('w')
    //         ->andWhere('w.exampleField = :val')
    //         ->setParameter('val', $value)
    //         ->getQuery()
    //         ->getOneOrNullResult();
    // }
}
