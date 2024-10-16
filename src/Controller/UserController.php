<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Information;
use App\Entity\Magasin;
use App\Entity\Message;
use App\Entity\PieceJointe;
use App\Entity\Status;
use App\Entity\Ticket;
use App\Entity\User;
use App\Entity\Avis;
use App\Entity\Notification;
use App\Entity\NotificationUser;
use App\Entity\UserMagasin;
use App\Form\MagasinType;
use App\Form\MessageType;
use App\Form\TicketType;
use App\Repository\TicketRepository;
use App\Repository\UserMagasinRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Paginator;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use Symfony\Component\HttpFoundation\File\Exception\FileException;


class UserController extends AbstractController
{
    private $repository;
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(TicketRepository $repository, EntityManagerInterface $em, private \Doctrine\Persistence\ManagerRegistry $managerRegistry)
    {
        $this->repository = $repository;
        $this->em = $em;
    }


    /**
     * @return Response
     */
    #[Route(path: '/user', name: 'user.index')]
    public function index(UserMagasinRepository $userMagasinRepository):Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $userId = $this->getUser()->getId();

        $user_magasin_info = $userMagasinRepository->findMagasinAndUserByUserId($userId);

        $magasinInfo = [];
        $ticketsPack=[];

        foreach ($user_magasin_info as $value)
        {
            $tickets = $this->managerRegistry->getRepository(Ticket::class)->findBy(['Mag'=>$value->getMagasin()]);
            
            array_push($magasinInfo, $value->getMagasin()); 
            array_push($ticketsPack, $tickets);   
        }

        $ticketsLast=[];

        foreach ($ticketsPack as $value)
        {
            foreach ($value as $item)
            {
                array_push($ticketsLast, $item);
            }
        }

        $tickets = $ticketsLast;

        $informations = $this->managerRegistry
            ->getRepository(Information::class)
            ->findBy(array('Active' => true), array('dateInsert'=>'DESC'));

        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'tickets'=>$tickets,
            'informations'=>$informations,
            'magasins' =>$magasinInfo,
        ]);
    }


    /**
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: 'user/tickets/{id}/message/add', name: 'user.ticket.show', methods: 'GET|POST')]
    public function addMessage( Ticket $ticket, Message $message)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');    
        $message->setTicket($ticket);
        $ticket->addMessage($message);
        //ajouter message au ticket sans form
    }


    /**
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: 'user/tickets/{id}', name: 'user.ticket.show')]
    public function show($id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        // We get current ticket by id
        $ticket = $this->managerRegistry->getRepository(Ticket::class)->find($id);
        $ticket->setMessageNonLu(0);

        $entityManager = $this->managerRegistry->getManager();
        $entityManager->persist($ticket);
        $entityManager->flush();

        $message =new Message();
        $form_message = $this->createForm(MessageType::class, $message);
        $form_message->handleRequest($request);

        if ($form_message->isSubmitted() && $form_message->isValid())
        {
            $message->setUser($this->getUser());
            $message->setDateRegister(new \DateTime());
            $message->setTicket($ticket);
	        
            $ticket->getMessage()->add($message);
	        $ticket->setMessageNonLuAdmin($ticket->getMessageNonLuAdmin() + 1);
            $ticket->setDateUpdate(new \DateTime());

            // We create a notification for new message on a ticket
            // This notification will be send to all admins
            $notification = new Notification();
            $notification->setTicket($ticket);
            $notification->setMagasin($ticket->getMag());
            $notification->setType('message');
            $notification->setCreatedAt(new \DateTimeImmutable());

            // We get the list of all admins
            $allAdmins = $this->managerRegistry->getRepository(User::class)->findBy(['Profil' => 1]);

            // We add a notification for each admin 
            foreach($allAdmins as $admin)
            {
                $notificationUser = new NotificationUser();
                $notificationUser->setNotification($notification);
                $notificationUser->setUser($admin);
                $notificationUser->setIsRead(false);
                $entityManager->persist($notificationUser);   
            }

            $entityManager = $this->managerRegistry->getManager();
            $entityManager->persist($ticket);
            $entityManager->persist($message);
            $entityManager->persist($notification);
            $entityManager->flush();

            return $this->redirectToRoute('user.ticket.show', ['id'=>$ticket->getId()]);
        }

        if (!$ticket) 
        {
            //return $this->render('Erreur');
            throw $this->createNotFoundException(
                'Pas de Tickets Trouvés ' . $id
            );
        }

        // We get all messages related to current ticket
        $messages =$this->managerRegistry->getRepository(Message::class)->findBy(['Ticket'=>$id]);
        
        return $this->render('user/User_TicketView.html.twig', [
                        'ticket' => $ticket,
                        'messages'=> $messages,
                        'form_message' => $form_message->createView(),
                    ]);

    }


    // creation avec formulaire EDITABLE
    /**
     * @param Request $request
     * @return
     * @throws \Exception
     */
    #[Route(path: 'user/ticket/create', name: 'user.create.ticket')]
    public function createTicket(Request $request, UserMagasinRepository $userMagasinRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $ticket = new Ticket();
        $message = new Message();

        $ticket->getMessage()->add($message);
        $message->setTicket($ticket);

        $userId = $this->getUser()->getId();

        // We get the list of all magasins related to current user
        $user_magasin_info = $userMagasinRepository->findMagasinAndUserByUserId($userId);

        $magasinInfo = [];

        foreach ($user_magasin_info as $value)
        {
            array_push($magasinInfo, $value->getMagasin()); 
        }

        // The array $magasinInfo contains the list of all magasins of current user

        $form = $this->createForm(TicketType::class, $ticket)
            ->add('Mag', EntityType::class, [ 
                'label' => 'Magasins',
                'class' => Magasin::class,
                'choice_label' => 'nom',
                'choices' => $magasinInfo,
                'expanded' => false
            ]);

        $form->handleRequest($request);

        $status =$this->managerRegistry->getRepository(Status::class)->find(1);
        $avis = $this->managerRegistry->getRepository(Avis::class)->find(1);

        if($form->isSubmitted() && $form->isValid())
        {
            $ChoixMag = $form->get("Mag")->getData();

            $ticket->setMag($ChoixMag);
            $ticket->setDateRegister(new \DateTime());
            $ticket->setStatus($status);
            $ticket->setUser($this->getUser());
            $ticket->setMessageNonLu(1);
            $ticket->setDateUpdate(new \DateTime());
            $ticket->setMessageNonLuAdmin(1);
            $ticket->setAvis($avis);
            
            $message->setUser($this->getUser());
            $message->setDateRegister(new \DateTime());

            // We a user creates a new ticket we create a notification
            $notification = new Notification();
            $notification->setTicket($ticket);
            $notification->setMagasin($ticket->getMag());
            $notification->setType('created');
            $notification->setCreatedAt(new \DateTimeImmutable());

            $entityManager = $this->managerRegistry->getManager();

            // This notification will be send to all users related to same magasin except the user who created the ticket and to all admins
            // So, we get the list of all users related to same magasin
            $allUsersOfMagasin = $userMagasinRepository->findMagasinAndUserByMagasinId($ChoixMag->getId());

            $allUsersOfMagasin_id = [];

            // We add a notification for each user related to same magasin
            foreach($allUsersOfMagasin as $item)
            {
                $notificationUser = new NotificationUser();
                $notificationUser->setNotification($notification);
                $notificationUser->setUser($item->getUser());
                $notificationUser->setIsRead(false);
                $entityManager->persist($notificationUser);

                array_push($allUsersOfMagasin_id, $item->getUser()->getId());
            }

            // We get the list of all admins
            $allAdmins = $this->managerRegistry->getRepository(User::class)->findBy(['Profil' => 1]);

            // We add a notification for each admin who is not among the users related to same magasin 
            foreach($allAdmins as $admin)
            {
                if(!in_array($admin->getId(), $allUsersOfMagasin_id)) 
                {
                    $notificationUser = new NotificationUser();
                    $notificationUser->setNotification($notification);
                    $notificationUser->setUser($admin);
                    $notificationUser->setIsRead(false);
                    $entityManager->persist($notificationUser);
                }
            }

            $entityManager->persist($ticket);
            $entityManager->persist($message);
            $entityManager->persist($notification);
            $entityManager->flush();

            // Envoi du mail
            $email =(new Email())
                ->from('support@cashconverters.fr')
                ->to($ticket->getUser()->getEmail())
                ->priority(Email::PRIORITY_HIGH)
                ->subject(' Support Cash Converters : Ticket n° '.$ticket->getId().' est crée')
                ->html('<p> Bonjour '.$ticket->getUser()->getLogin().', <br><br> Votre ticket n°'.$ticket->getId().' a été créé avec succès.<br> Catégorie '.$ticket->GetCategorie().'<br> Message : '.$ticket->GetMessage()[0].'<br>Votre demande a été transmise au technicien en charge de votre ticket. Nous reviendrons vers vous dans les meilleurs délais. <br> Cordialement,<br> <br> Service Support,<br><b>Cash Converters</b></p>');

            //$sentEmail = $mailer->send($email);
	
            return $this->redirectToRoute('user.ticket.show', ['id'=> $ticket->getId()]);
        }

        return $this->render('user/User_create_ticket.html.twig', [
            'form' => $form->createView(),
        ]);

    }


    // <------------------------- Page Admin Ticket.Resolve -------------------------->
    /**
     * @param $id, $note
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    #[Route(path: 'user/tickets/{id}/{note}', name: 'user.tickets.satisfaction')]
    public function satisfaction_tickets($id, $note, Request $request )
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        // $this->denyAccessUnlessGranted('ROLE_ROOT');
        $ticket = $this->managerRegistry->getRepository(Ticket::class)->find($id);
        $avis = $this->managerRegistry->getRepository(Avis::class)->find($note);;
        $ticket->setAvis($avis);
        // en base de donnée

        $entityManager = $this->managerRegistry->getManager();
        $entityManager->persist($ticket);
        $entityManager->flush();
        // redirection vers show

        return $this->render('user/User_ticket_satisfaction.html.twig');
    }
}
