<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\Table(name: 'post', uniqueConstraints: [
    new ORM\UniqueConstraint(name: 'uniq_post_date_isdel', columns: ['date', 'is_deleted'])
])]
#[UniqueEntity(fields: ['date', 'isDeleted'], message: 'この日付の投稿は既に存在します。')]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 1000)]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: false)]
    private ?\DateTime $date = null;

    // Post : PostSubject = 1 : N
    #[ORM\OneToMany(mappedBy: 'post', targetEntity: PostSubject::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $postSubjects;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private ?bool $isDeleted = false;

    public function __construct()
    {
        $this->postSubjects = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    /** @return Collection<int, PostSubject> */
    public function getPostSubjects(): Collection { return $this->postSubjects; }

    public function addPostSubject(PostSubject $ps): static
    {
        if (!$this->postSubjects->contains($ps)) {
            $this->postSubjects->add($ps);
            $ps->setPost($this);
        }
        return $this;
    }

    public function removePostSubject(PostSubject $ps): static
    {
        if ($this->postSubjects->removeElement($ps) && $ps->getPost() === $this) {
            $ps->setPost(null);
        }
        return $this;
    }

    public function isDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): static
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

}
