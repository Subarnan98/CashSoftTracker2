<?php

namespace App\Entity;

use App\Entity\Ticket;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: \App\Entity\User::class)]
    private $User;

    #[ORM\Column(type: 'text')]
    private $Message;

    
    #[ORM\ManyToOne(targetEntity: \App\Entity\Ticket::class, inversedBy: 'Message')]
    private $Ticket;


    #[ORM\Column(type: 'datetime')]
    private $date_register;


    public function getDateRegister(): ?\DateTimeInterface
    {
        return $this->date_register;
    }

    public function setDateRegister(\DateTimeInterface $DateRegister): self
    {
        $this->date_register = $DateRegister;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(User $User): self
    {
        $this->User = $User;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->Message;
    }

    public function setMessage(string $Message): self
    {
        $this->Message = $Message;

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

    public function __toString()
    {
        return $this->Message;
    }


}
