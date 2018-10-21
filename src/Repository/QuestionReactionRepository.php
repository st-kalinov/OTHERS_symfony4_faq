<?php

namespace App\Repository;

use App\Entity\QuestionAnswer;
use App\Entity\QuestionReaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method QuestionReaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method QuestionReaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method QuestionReaction[]    findAll()
 * @method QuestionReaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuestionReactionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, QuestionReaction::class);
    }


    public function getQuestionReactions(QuestionAnswer $questionAnswer)
    {
        return $this->createQueryBuilder('qr')
            ->leftJoin('qr.reaction', 'react')
            ->select('react.reason')
            ->andWhere('qr.question = :q')
            ->setParameter('q', $questionAnswer->getId())
            ->getQuery()
            ->getResult();

    }

    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('q.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?QuestionReaction
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
