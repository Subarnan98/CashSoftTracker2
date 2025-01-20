<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Fichier;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: \App\Repository\TicketRepository::class)]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['notification'])]
    private $id;

    #[ORM\ManyToOne(targetEntity: Categorie::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $Categorie;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private $User;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\Length(min: 2, max: 255, minMessage: 'Votre titre est trop court {{ limit }} caractères', maxMessage: 'Votre titre est trop long {{ limit }} caractères')]
    #[Groups(['notification'])]
    private $Titre;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'Ticket', cascade: ['all'], orphanRemoval: true)]
    private Collection $Message;

    #[ORM\ManyToOne(targetEntity: Magasin::class)]
    #[ORM\JoinColumn(name: 'mag_id', referencedColumnName: 'id')]
    private $Mag;

    #[ORM\ManyToOne(targetEntity: Status::class)]
    #[ORM\JoinColumn(name: 'status_id', referencedColumnName: 'id')]
    #[Groups(['notification'])]
    private $Status;

    #[ORM\Column(type: 'string', nullable: true)]
    private $IdTeamVw;

    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    private $CodeTeamWV;
   
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Groups(['notification'])]
    private $Admin;

    #[ORM\Column(type: 'datetime')]
    private $DateRegister;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $DateResolve;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $DateClosed;

    #[ORM\Column(type: 'integer')]
    private $MessageNonLu;

    #[ORM\Column(type: 'integer')]
    private $MessageNonLuAdmin;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $DateUpdate;

    #[Groups(['notification'])]
    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private $Nom;

    #[Groups(['notification'])]
    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private $Prenom;

    #[ORM\ManyToOne(targetEntity: Avis::class, inversedBy: 'ticket')]
    #[ORM\JoinColumn(name: 'avis_id', referencedColumnName: 'id')]
    private $avis;

    /**
     * @var Collection<int, Notification>
     */
    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'ticket', orphanRemoval: true)]
    private Collection $notifications;

    /**
     * @var Collection<int, Fichier>
     */
    #[ORM\OneToMany(targetEntity: Fichier::class, mappedBy: 'ticket', orphanRemoval: true, cascade: ['persist'])]
    private Collection $fichiers;

   

    public function __construct()
    {
        $this->Message = new ArrayCollection();
        $this->fichiers = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->Categorie;
    }

    public function setCategorie(Categorie $Categorie): self
    {
        $this->Categorie = $Categorie;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): self
    {
        $this->User = $User;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->Titre;
    }

    public function setTitre(string $Titre): self
    {
        $this->Titre = $Titre;

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessage(): Collection
    {
        return $this->Message;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->Message->contains($message)) {
            $this->Message[] = $message;
            $message->setTicket($this);

        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->Message->contains($message)) {
            $this->Message->removeElement($message);
            // set the owning side to null (unless already changed)
            if ($message->getTicketId() === $this) {
                $message->setTicketId(null);
            }
        }

        return $this;
    }

    public function getMag(): ?Magasin
    {
        return $this->Mag;
    }

    public function setMag(Magasin $Mag): self
    {
        $this->Mag = $Mag;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->Status;
    }

    public function setStatus(Status $Status): self
    {
        $this->Status = $Status;

        return $this;
    }

    public function getIdTeamVw(): ?string
    {
        return $this->IdTeamVw;
    }

    public function setIdTeamVw(?string $IdTeamVw): self
    {
        $this->IdTeamVw = $IdTeamVw;

        return $this;
    }

    public function getCodeTeamWV(): ?string
    {
        return $this->CodeTeamWV;
    }

    public function setCodeTeamWV(?string $CodeTeamWV): self
    {
        $this->CodeTeamWV = $CodeTeamWV;

        return $this;
    }

    public function getAdmin(): ?User
    {
        return $this->Admin;
    }

    public function setAdmin(?User $Admin): self
    {
        $this->Admin = $Admin;

        return $this;
    }

    public function getDateRegister(): ?\DateTimeInterface
    {
        $DateRegister = $this->DateRegister;
        return $DateRegister;
    }

    public function setDateRegister(\DateTimeInterface $DateRegister): self
    {
        $this->DateRegister = $DateRegister;

        $day = $DateRegister->format('d');
        $month = $DateRegister->format('m');
        $year = $DateRegister->format('Y');
        
        $DateRegister = $day. '-'.$month.'-'.$year;

        return $this;
    }

    public function getDateClosed(): ?\DateTimeInterface
    {
        return $this->DateClosed;
    }

    public function setDateClosed(?\DateTimeInterface $DateClosed): self
    {
        $this->DateClosed = $DateClosed;

        return $this;
    }

    public function getDateResolve(): ?\DateTimeInterface
    {
        return $this->DateResolve;
    }

    public function setDateResolve(?\DateTimeInterface $DateResolve): self
    {
        $this->DateResolve = $DateResolve;

        return $this;
    }

   public function getMessageNonLu(): ?int
    {
        return $this->MessageNonLu;
    }

    public function setMessageNonLu(int $MessageNonLu): self
    {
        $this->MessageNonLu = $MessageNonLu;

        return $this;
    }

    public function getMessageNonLuAdmin(): ?int
    {
        return $this->MessageNonLuAdmin;
    }

    public function setMessageNonLuAdmin(int $MessageNonLuAdmin): self
    {
        $this->MessageNonLuAdmin = $MessageNonLuAdmin;

        return $this;
    }

    public function getDateUpdate(): ?\DateTimeInterface
    {
        return $this->DateUpdate;
    }

    public function setDateUpdate(?\DateTimeInterface $DateUpdate): self
    {
        $this->DateUpdate = $DateUpdate;

        return $this;
    }


    /**
     * @return Collection|Fichier[]
     */
    public function getFichiers(): Collection
    {
        return $this->fichiers;
    }

    public function addFichier(Fichier $fichier): self
    {
        if (!$this->fichiers->contains($fichier)) 
        {
            $this->fichiers[] = $fichier;
            $fichier->setTicket($this);
        }

        return $this;
    }

    public function removeFichier(Fichier $fichier): self
    {
        if ($this->fichiers->contains($fichier)) {
            $this->fichiers->removeElement($fichier);
            // set the owning side to null (unless already changed)
            if ($fichier->getTicket() === $this) {
                $fichier->setTicket(null);
            }
        }

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->Nom;
    }

    public function setNom(?string $Nom): self
    {
        $this->Nom = $Nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->Prenom;
    }

    public function setPrenom(?string $Prenom): self
    {
        $this->Prenom = $Prenom;

        return $this;
    }

    public function __toString()
    {
        return $this->DateRegister;
    }

    public function getAvis(): ?Avis
    {
        return $this->avis;
    }

    public function setAvis(?Avis $avis): self
    {
        $this->avis = $avis;

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setTicket($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getTicket() === $this) {
                $notification->setTicket(null);
            }
        }

        return $this;
    }

   

   

  

  
}
