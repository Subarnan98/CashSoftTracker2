<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: \App\Repository\ProfilRepository::class)]
class Profil
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 12)]
    #[Groups(['notification'])]
    private $Profil;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProfil(): ?string
    {
        return $this->Profil;
    }

    public function setProfil(string $Profil): self
    {
        $this->Profil = $Profil;

        return $this;
    }

    public function __toString()
    {
        return $this->getProfil();
    }
}
