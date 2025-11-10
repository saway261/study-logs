<?php

namespace App\Repository;

use App\Entity\SubjectStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class SubjectStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubjectStatus::class);
    }

    /**
     * 「学習中（id=1）」を取得。
     */
    public function getStudying(): SubjectStatus
    {
        return $this->getEntityManager()->getReference(SubjectStatus::class, 1);
    }

    /**
     * 「学習完了（id=2）」を取得。
     */
    public function getDone(): SubjectStatus
    {
        return $this->getEntityManager()->getReference(SubjectStatus::class, 2);
    }
}
