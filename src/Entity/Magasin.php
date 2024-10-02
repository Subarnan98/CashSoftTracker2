<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: \App\Repository\MagasinRepository::class)]
class Magasin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 64)]
    #[Groups(['notification'])]
    private $Nom;

    
    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\Email(message: " l'email '{{ value }}' n'est pas valide.")]
    private $Email;

    
    #[ORM\Column(type: 'integer')]
    private $Tel;

    
    #[ORM\Column(type: 'string', length: 64)]
    #[Assert\Length(min: 2, max: 50, minMessage: ' nom du Responsable, {{ limit }} trop court', maxMessage: ' nom du responsable, {{ limit }} trop long')]
    private $RespName;

    #[ORM\ManyToMany(targetEntity: \App\Entity\User::class, mappedBy: 'Magasin')]
    private $users;

    #[ORM\Column(type: 'string', length: 24, nullable: true)]
    private $Portable;

    #[ORM\Column(type: 'text', nullable: true)]
    private $licence;

    /**
     * @var Collection<int, Notification>
     */
    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'magasin')]
    private Collection $notifications;



    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->Nom;
    }

    public function setNom(string $Nom): self
    {
        $this->Nom = $Nom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->Email;
    }

    public function setEmail(string $Email): self
    {
        $this->Email = $Email;

        return $this;
    }

    public function getTel(): ?int
    {
        return $this->Tel;
    }

    public function setTel(int $Tel): self
    {
        $this->Tel = $Tel;

        return $this;
    }

    public function getRespName(): ?string
    {
        return $this->RespName;
    }

    public function setRespName(string $RespName): self
    {
        $this->RespName = $RespName;

        return $this;
    }

    public function getUserId(): ?User
    {
        return $this->UserId;
    }

    public function setUserId(?User $UserId): self
    {
        $this->UserId = $UserId;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addMag($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            $user->removeMag($this);
        }

        return $this;
    }




    public function __toString()
    {
        return $this->getNom();
    }

    public function getPortable(): ?int
    {
        return $this->Portable;
    }

    public function setPortable(?int $Portable): self
    {
        $this->Portable = $Portable;

        return $this;
    }

    public function getLicence(): ?string
    {
        return $this->licence;
    }

    public function setLicence(?string $licence): self
    {
        $this->licence = $licence;

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
            $notification->setMagasin($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getMagasin() === $this) {
                $notification->setMagasin(null);
            }
        }

        return $this;
    }
}
