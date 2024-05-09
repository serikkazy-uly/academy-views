<?php

namespace App\Entity;

use App\Repository\ViewRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ViewRepository::class)]
class EntityViewCount
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $entityId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $entityType = null;

    #[ORM\Column(nullable: true)]
    private ?int $pageViews = null;

    #[ORM\Column(nullable: true)]
    private ?int $phoneViews = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    public function setEntityId(?int $entityId): static
    {
        $this->entityId = $entityId;

        return $this;
    }

    public function getEntityType(): ?string
    {
        return $this->entityType;
    }

    public function setEntityType(?string $entityType): static
    {
        $this->entityType = $entityType;

        return $this;
    }

    public function getPageViews(): ?int
    {
        return $this->pageViews;
    }

    public function setPageViews(?int $pageViews): static
    {
        $this->pageViews = $pageViews;

        return $this;
    }

    public function getPhoneViews(): ?int
    {
        return $this->phoneViews;
    }

    public function setPhoneViews(?int $phoneViews): static
    {
        $this->phoneViews = $phoneViews;

        return $this;
    }
}
