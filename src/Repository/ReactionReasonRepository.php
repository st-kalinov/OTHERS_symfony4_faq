<?php

namespace App\Repository;

use App\Entity\ReactionReason;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ReactionReason|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReactionReason|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReactionReason[]    findAll()
 * @method ReactionReason[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReactionReasonRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ReactionReason::class);
    }

    public function getReasonsNames(): array
    {
        $objects = $this->findAll();
        $reactions = [];
        foreach ($objects as $obj)
        {
            $reactions[$obj->getId()] = $obj->getReason();
        }

        return $reactions;

    }
//    /**
//     * @return ReactionReason[] Returns an array of ReactionReason objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ReactionReason
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
