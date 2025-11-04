<?php

namespace App\Repository;

use App\Entity\Subject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class SubjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subject::class);
    }

    /** 学習中(status_id=1) かつ 未削除 を name昇順で */
    public function findStudying(): array
    {
        return $this->qbActiveByStatus(1)
            ->addOrderBy('s.name', 'ASC')
            ->getQuery()->getResult();
    }

    /** 学習完了(status_id=2) かつ 未削除 を name昇順で */
    public function findDone(): array
    {
        return $this->qbActiveByStatus(2)
            ->addOrderBy('s.name', 'ASC')
            ->getQuery()->getResult();
    }

    /** 共通：未削除の指定ステータスで絞るQB */
    private function qbActiveByStatus(int $statusId)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.isDeleted = :deleted')->setParameter('deleted', false)
            // status は ManyToOneなので id で比較（JOIN不要でOK）
            ->andWhere('IDENTITY(s.status) = :statusId')->setParameter('statusId', $statusId);
    }
}
