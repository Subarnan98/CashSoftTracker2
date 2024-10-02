<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['notification'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'notifications')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['notification'])]
    private ?Ticket $ticket = null;
    
    #[ORM\ManyToOne(inversedBy: 'notifications')]
    #[Groups(['notification'])]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'notifications')]
    #[Groups(['notification'])]
    private ?Magasin $magasin = null;

    #[ORM\Column(length: 255)]
    #[Groups(['notification'])]
    private ?string $type = null;   // "created" or "status_changed"

    #[ORM\Column]
    #[Groups(['notification'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['notification'])]
    private ?bool $isRead = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTicket(): ?Ticket
    {
        return $this->ticket;
    }

    public function setTicket(?Ticket $ticket): static
    {
        $this->ticket = $ticket;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getMagasin(): ?Magasin
    {
        return $this->magasin;
    }

    public function setMagasin(?Magasin $magasin): static
    {
        $this->magasin = $magasin;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->createdAt = $created_at;

        return $this;
    }

    public function getIsRead(): ?bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $is_read): static
    {
        $this->isRead = $is_read;

        return $this;
    }
}
