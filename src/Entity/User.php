<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: \App\Repository\UserRepository::class)]
#[UniqueEntity('Login')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: \App\Entity\Profil::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['notification'])]
    protected $Profil;

    #[ORM\Column(type: 'string', length: 32)]
    #[Assert\Length(min: 3, max: 32, minMessage: ' {{ limit }} caractères, Votre Nom est trop Court ', maxMessage: ' {{ limit }} caractères, Votre Nom est trop Long ')]
    #[Groups(['notification'])]
    private $Nom;
    
    #[ORM\Column(type: 'string', length: 34)]
    #[Assert\Length(min: 3, max: 34, minMessage: ' {{ limit }} caractères, Votre Nom est trop Court ', maxMessage: ' {{ limit }} caractères, Votre Nom est trop Long ')]
    #[Groups(['notification'])]
    private $Prenom;
    
    #[ORM\Column(type: 'string', length: 64)]
    #[Assert\Length(min: 3, max: 64, minMessage: ' {{ limit }} caractères, Votre Login est trop Court ', maxMessage: ' {{ limit }} caractères, Votre Login est trop Long ')]
    private $Login;

    #[ORM\Column(type: 'string', length: 255)]
    private $Password;

    #[ORM\Column(type: 'boolean')]
    private $bDel = 0;

    #[ORM\Column(type: 'datetime')]
    private $DateRegister;

    #[ORM\Column(type: 'string', length: 64)]
    #[Assert\Email(message: "l'email '{{ value }}' n'est pas valide.")]
    private $Email;

    /**
     * @var Collection<int, NotificationUser>
     */
    #[ORM\OneToMany(targetEntity: NotificationUser::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $notificationUsers;

    /**
     * @var Collection<int, UserMagasin>
     */
    #[ORM\OneToMany(targetEntity: UserMagasin::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $userMagasins;


    public function __construct()
    {
        $this->notificationUsers = new ArrayCollection();
        $this->userMagasins = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProfil()
    {
        return $this->Profil;
    }

    public function setProfil(?Profil $Profil): self
    {
        $this->Profil = $Profil;

        return $this;
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

    public function getPrenom(): ?string
    {
        return $this->Prenom;
    }

    public function setPrenom(string $Prenom): self
    {
        $this->Prenom = $Prenom;

        return $this;
    }

    public function getLogin(): ?string
    {
        return $this->Login;
    }

    public function setLogin(string $Login): self
    {
        $this->Login = $Login;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->Password;
    }

    public function setPassword(string $Password): self
    {
        $this->Password = $Password;

        return $this;
    }

    public function getBDel()
    {
        return $this->bDel;
    }

    public function setBDel($bDel): self
    {
        $this->bDel = $bDel;

        return $this;
    }

    public function getDateRegister(): ?\DateTimeInterface
    {
        return $this->DateRegister;
    }

    public function setDateRegister(\DateTimeInterface $DateRegister): self
    {
        $this->DateRegister = $DateRegister;

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

    /**
     * @inheritDoc
     */
    public function getRoles(): array
    {
        /*$roles[]= 'ROLE_USER';

        return array_unique($roles);
        */

        $id_role = $this->getProfil()->getId();

        if ($id_role == 4)
        {
            $role = ['ROLE_USER'];
        }
        else
        {
            if($id_role == 1)
            {
                $role = ['ROLE_ADMIN'];
            }
            else
            {
                $role =['ROLE_NONE'];

            }
        }

        return $role;
    }

    /**
     * @inheritDoc
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getUserIdentifier(): string
    {
        return $this->Login;
    }
    

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return serialize([$this->id, $this->Login, $this->Password]);
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->Login,
            $this->Password
            ) = unserialize($serialized,['allowed_classes'=>false]);
    }

    public function __toString()
    {
        return $this->getLogin();
    }

    /**
     * @return Collection<int, NotificationUser>
     */
    public function getNotificationUsers(): Collection
    {
        return $this->notificationUsers;
    }

    public function addNotificationUser(NotificationUser $notificationUser): static
    {
        if (!$this->notificationUsers->contains($notificationUser)) {
            $this->notificationUsers->add($notificationUser);
            $notificationUser->setUser($this);
        }

        return $this;
    }

    public function removeNotificationUser(NotificationUser $notificationUser): static
    {
        if ($this->notificationUsers->removeElement($notificationUser)) {
            // set the owning side to null (unless already changed)
            if ($notificationUser->getUser() === $this) {
                $notificationUser->setUser(null);
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
            $userMagasin->setUser($this);
        }

        return $this;
    }

    public function removeUserMagasin(UserMagasin $userMagasin): static
    {
        if ($this->userMagasins->removeElement($userMagasin)) {
            // set the owning side to null (unless already changed)
            if ($userMagasin->getUser() === $this) {
                $userMagasin->setUser(null);
            }
        }

        return $this;
    }


}
