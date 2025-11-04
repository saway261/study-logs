<?php

namespace App\Entity;

use App\Repository\SubjectStatusRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubjectStatusRepository::class)]
#[ORM\Table(name: 'subject_status')]
#[ORM\UniqueConstraint(name: 'uniq_subject_status_name', columns: ['status'])]
class SubjectStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private ?string $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
