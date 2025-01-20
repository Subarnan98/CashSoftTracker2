<?php

namespace App\Entity;

use App\Repository\FichierRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;


// For file upload we use VichUploaderBundle: https://github.com/dustin10/VichUploaderBundle/blob/master/docs/index.md
#[ORM\Entity(repositoryClass: FichierRepository::class)]
#[Vich\Uploadable]  // we annotate Fichier class with the "Uploadable" attribute indicating that this entity contains uploadable fields.
class Fichier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    // $filename property will be stored to the database as a string. This will hold the filename of the uploaded file.
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $filename = null;

    /**
     * $File property is not mapped. It will store the UploadedFile object after the form is submitted. This property should not be persisted to the database.
     * mapping:'ticket_file' - this is the mapping name specified in the configuration file: config/packages/vich_uploader.yaml
     * fileNameProperty:'filename' - it refers to $filename property that will contain the name of the uploaded file.
    */
    // #[Vich\UploadableField(mapping: 'ticket_file', fileNameProperty: 'filename')]
    // #[Assert\File(
    //     maxSize: '10M',
    //     mimeTypes: ['application/pdf', 'image/png', 'image/jpeg', 'image/jpg', 'application/csv', 'text/plain'],
    //     mimeTypesMessage: 'Veuillez télécharger un fichier PDF, PNG, JPEG, JPG, CSV ou texte valide'
    // )]
    // private ?File $file = null;

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

    // public function getFile(): ?File
    // {
    //     return $this->file;
    // }
    
    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $File
     */
    // public function setFile(?File $file): void
    // {
    //     $this->file = $file;

    //     if (null !== $file) 
    //     {
    //         // It is required that at least one field changes if you are using doctrine
    //         // otherwise the event listeners won't be called and the file is lost
    //         $this->save_at = new \DateTime();  // Ensures the entity is updated
    //     }
    // }


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
