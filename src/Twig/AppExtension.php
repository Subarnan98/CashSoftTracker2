<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Ticket;

use App\Repository\TicketRepository;
use App\Repository\MagasinRepository;
class AppExtension extends AbstractExtension
{
    public function datedifffilter( \DateTime $dateRegister, \DateTime $dateResolve)
    {
        $dateRegister = $this->getDoctrine()->getRepository(Ticket::class)->find($dateRegister);
    }
    
}

















?>