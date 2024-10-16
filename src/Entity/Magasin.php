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

    #[ORM\Column(type: 'string', length: 24, nullable: true)]
    private $Portable;

    #[ORM\Column(type: 'text', nullable: true)]
    private $licence;

    /**
     * @var Collection<int, Notification>
     */
    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'magasin')]
    private Collection $notifications;

    /**
     * @var Collection<int, UserMagasin>
     */
    #[ORM\OneToMany(targetEntity: UserMagasin::class, mappedBy: 'magasin', orphanRemoval: true)]
    private Collection $userMagasins;


    public function __construct()
    {
        $this->notifications = new ArrayCollection();
        $this->userMagasins = new ArrayCollection();
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

    /**
     * @return Collection<int, UserMagasin>
     */
    public function getUserMagasins(): Collection
    {
        return $this->userMagasins;
    }

    public function addUserMagasin(UserMagasin $userMagasin): static
    {
        if (!$this->userMagasins->contains($userMagasin)) {
            $this->userMagasins->add($userMagasin);
            $userMagasin->setMagasin($this);
        }

        return $this;
    }

    public function removeUserMagasin(UserMagasin $userMagasin): static
    {
        if ($this->userMagasins->removeElement($userMagasin)) {
            // set the owning side to null (unless already changed)
            if ($userMagasin->getMagasin() === $this) {
                $userMagasin->setMagasin(null);
            }
        }

        return $this;
    }
}
