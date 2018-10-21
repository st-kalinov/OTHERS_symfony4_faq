<?php

namespace App\Repository;

use App\Entity\ReactionReason;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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

    /**
     * Array with keys "MainCategories" and subkeys "reason name" and values "count"
     *
     * @return array
     */
    public function getReasonsNamesAsCategories(): array
    {
        $reactions = $this->getMainCategories();
        $objects = $this->findAll();

        foreach ($objects as $obj)
        {
            $reactions[$obj->getReactionCategory()][$obj->getReason()] = 0;
        }

        return $reactions;
    }

    public function getMainCategories(): array
    {
        $objects = $this->createQueryBuilder('a')
            ->distinct()
            ->select('a.reaction_category')
            ->getQuery()
            ->getResult();

        $reactions = [];
        foreach ($objects as $obj)
        {
            foreach ($obj as $val)
            {
                $reactions[$val] = null;
            }
        }

        return $reactions;
    }

    public function getReactionsObjAsCategories()
    {
        $categories = $this->getMainCategories();
        $reactions = [];

        foreach ($categories as $category => $val)
        {
            $reactions[$category] = $this->findBy(['reaction_category' => $category]);
        }

        return $reactions;
    }

    private function getOrCreateQueryBuilder(QueryBuilder $qb = null)
    {
        return $qb ?: $this->createQueryBuilder('a');
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
