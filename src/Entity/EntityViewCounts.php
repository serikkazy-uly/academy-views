<?php

namespace App\Entity;

use App\Repository\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EntityRepository::class)]
class EntityViewCounts
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $entityId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $entity = null;

    #[ORM\Column(nullable: true)]
    private ?int $pageViews = 0;

    #[ORM\Column(nullable: true)]
    private ?int $phoneViews = 0;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $project;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    public function setEntityId(?int $entityId): self
    {
        $this->entityId = $entityId;

        return $this;
    }

    public function getEntity(): ?string
    {
        return $this->entity;
    }

    public function setEntity(?string $entity): self
    {
        $this->entity = $entity;

        return $this;
    }

    public function getPageViews(): ?int
    {
        return $this->pageViews;
    }

    public function setPageViews(?int $pageViews): self
    {
        $this->pageViews = $pageViews;

        return $this;
    }

    public function getPhoneViews(): ?int
    {
        return $this->phoneViews;
    }

    public function setPhoneViews(?int $phoneViews): self
    {
        $this->phoneViews = $phoneViews;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function setProject(string $project): self
    {
        $this->project = $project;
        return $this;
    }
    public  function getProject(): string
    {
        return $this->project;
    }
}
