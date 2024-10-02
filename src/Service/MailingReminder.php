<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

use App\Entity\Ticket;
use App\Entity\Status;
use App\Entity\Magasin;

use App\Repository\TicketRepository;
use App\Repository\StatusRepository;
use App\Repository\MagasinRepository;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MailingReminder extends AbstractController
{
    private $repository;
            /**
             * @var EntityManagerInterface
             */

            private $repositoryMag;
            private $em;


            public function __construct(TicketRepository $repository, EntityManagerInterface $em, MagasinRepository $repositoryMag, private \Doctrine\Persistence\ManagerRegistry $managerRegistry)
            {
                $this->repository = $repository;
                $this->em = $em;
                $this->repositoryMag =$repositoryMag;
            }

     /**
         * @param $id, $DateRegister
         * @return Response
         * @throws \Exception
         */
        #[Route(path: 'admin/tickets/{id}/rappel', name: 'admin.tickets.rappel')]
        public function rappelTickets($id,MailerInterface $mailer)
        {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');


            $ticket = $this->managerRegistry->getRepository(Ticket::class)->find($id);
            $DateRegister = $ticket->getDateRegister();

            $StatusOuvert = $this->managerRegistry->getRepository(Status::class)->find(2);

            $dtToday= date_create('today');
            //$interval= date_diff($ticketDtCree,$dtToday);
            $interval= date_diff($DateRegister,$dtToday);
            $valdiff =  (int)$interval->format("%a");
            if (($StatusOuvert->getId() == 2) && ($valdiff >= 7))
             {
                $email =(new Email())
                ->from('noreply@siege.cashconverters.fr')
                ->to($ticket->getUser()->getEmail())
                ->cc('support@siege.cashconverters.fr')
                ->priority(Email::PRIORITY_HIGH)
                ->subject('Support Cash Converters : Ticket n° '.$ticket->getId().' est toujours en traitement !')
                ->html('<p> Bonjour '.$ticket->getUser()->getLogin().', <br><br> Votre ticket n°'.$ticket->getId().' est toujours en cours de traitement. <br> <br> <b> <u> Catégorie : </u></b>'.$ticket->GetCategorie().'<br> <br> Cordialement,<br> <b>Support Cash Converters</b></p>');

            $sentEmail = $mailer->send($email); 

            return $this->redirectToRoute('admin.ticket.show',['id'=>$ticket->getId()]);
            } 
            
            return $this->redirectToRoute('admin.tickets.all');
        }
}
























?>