<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Types\Types;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /** 記事詳細用：その日付の1件（postSubjectsを一緒に取得） */
    public function findByDate(\DateTimeInterface $date): ?Post
    {
        return $this->createQueryBuilder('p')
        ->leftJoin('p.postSubjects', 'ps')->addSelect('ps')
        ->leftJoin('ps.subject', 's')->addSelect('s')
        ->andWhere('p.date = :date')
        ->andWhere('p.isDeleted = false')
        ->setParameter('date', $date, Types::DATE_IMMUTABLE) // ← ここで文字列でもOK
        ->getQuery()
        ->getOneOrNullResult();
    }

}
