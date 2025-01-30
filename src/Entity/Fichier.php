<?php

namespace App\Entity;

use App\Repository\FichierRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FichierRepository::class)]
class Fichier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $filename = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $save_at = null;

    #[ORM\ManyToOne(targetEntity: Ticket::class, inversedBy: 'fichiers')]
    private ?Ticket $ticket;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $tempId = null;


    public function __construct()
    {
        $this->save_at = new \DateTime();  // we assign a default value to the property $save_at
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getTicket(): ?Ticket
    {
        return $this->ticket;
    }

    public function setTicket(?Ticket $ticket): self
    {
        $this->ticket = $ticket;

        return $this;
    }

    public function getSaveAt(): ?\DateTimeInterface
    {
        return $this->save_at;
    }

    public function setSaveAt(?\DateTimeInterface $save_at): self
    {
        $this->save_at = $save_at;

        return $this;
    }

    public function getTempId(): ?string
    {
        return $this->tempId;
    }

    public function setTempId(?string $tempId): self
    {
        $this->tempId = $tempId;
        return $this;
    }

}
