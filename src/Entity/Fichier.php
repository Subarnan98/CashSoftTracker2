<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @Vich\Uploadable()
 */
#[ORM\Entity(repositoryClass: \App\Repository\FichierRepository::class)]
class Fichier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    /**
     * @var string|null
     *
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $filename;

    /**
     * @var null|File
     * @Vich\UploadableField(mapping="ticket_file", fileNameProperty="filename")
     */
    #[Assert\File(maxSize: '4M', mimeTypes: ['application/pdf', 'application/csv', 'text/plain', 'image/jpeg', 'image/png'])]
    protected $File;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $save_at;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Ticket::class, inversedBy: 'Fichiers')]
    private $Ticket;


    public function __construct()
    {
        $this->save_at = new \DateTime();
    }



    public function getnamefile()
    {
        return $this->save_at->format('Y-m-d-H-i-s');
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

    public function getFile(): ?File
    {
        return $this->File;
    }

    /**
     * @param mixed $File
     * @throws \Exception
     */
    public function setFile(?File $File): self
    {
        $this->File = $File;
        return $this;
    }


    public function getTicket(): ?Ticket
    {
        return $this->Ticket;
    }

    public function setTicket(?Ticket $Ticket): self
    {
        $this->Ticket = $Ticket;

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

}
