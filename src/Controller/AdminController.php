<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Categorie;
use App\Entity\Fichier;
use App\Entity\Information;
use App\Entity\Magasin;
use App\Entity\Message;
use App\Entity\Notification;
use App\Entity\NotificationUser;
use App\Entity\Profil;
use App\Entity\Status;
use App\Entity\Ticket;
use App\Entity\User;
use App\Form\CategorieType;
use App\Form\InformationType;
use App\Form\MagasinType;
use App\Form\MessageType;
use App\Form\UserType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use Knp\Component\Pager\Pagination\PaginationInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use App\Form\TicketType;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;

use Symfony\Component\Form\FormTypeInterface;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Form\Extension\Core\Type\{CollectionType,
    FormType,
    TextType,
    ChoiceType,
    FileType,
    ButtonType,
    EmailType,
    PasswordType,
    TextareaType,
    SubmitType,
    NumberType,
    DateType,CheckboxType, DateTimeType as TypeDateTimeType};
use Symfony\Component\HttpFoundation\JsonResponse;


class AdminController extends AbstractController
{
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }


    //<------------------------------ Page Index Admin --------------------------------------->
    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    #[Route(path: '/admin', name: 'admin.index')]
    public function index(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $MagAll = $this->managerRegistry
            ->getRepository(Magasin::class)->createQueryBuilder('m')
            ->orderBy('m.Nom','ASC')
            ->getQuery()
            ->getResult();

        $MagasinChoice = new Magasin();

        $searchForm = $this->createForm(MagasinType::class, $MagasinChoice);
        $searchForm  ->add('Nom', EntityType::class, [ 'label'=> 'Magasin','class' => Magasin::class,
            'choice_label' => function ($MagAll) {
            return $MagAll->getID() . " - " . $MagAll->getNom();
            },
            'choice_value'=>'Nom',
            'placeholder'=>'Choisissez ...'
        ]);

        $searchForm->handleRequest($request);

        // <----------------------- Admin.Index DashBoard --------------------------->
        /* Nb Tickets non Résolu */
        $ticketsNOK= $this->managerRegistry->getRepository(Ticket::class)->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.Status=1')
            ->getQuery()
            ->getSingleScalarResult();
        
        /* Nb tickets satisfait et non satisfait
        $ticketsSatisfaction= $this->getDoctrine()->getRepository(Avis::class)->createQueryBuilder('a')
        ->select('SUM(case t.avis_id when 1 then 1 else 0 end) as status_indeterminer, SUM(case t.avis_id when 2 then 1 else 0 end) as status_satisfait, SUM(case t.avis_id when 5 then 1 else 0 end) as status_insatisfait, SUM(case a.avis when 3 then 1 else 0 end) as status_neutre')
        ->getQuery()
        ->getSingleScalarResult();*/
        $ticketsSatisfaction= $this->getSatisfaction();
        /* Nb Tickets en Attente  */
        $ticketsW= $this->managerRegistry->getRepository(Ticket::class)->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.Status = 2')
            ->getQuery()
            ->getSingleScalarResult();

        /* Nb Tickets Résolu  */
        $ticketsOK= $this->managerRegistry->getRepository(Ticket::class)->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.Status = 4')
            ->getQuery()
            ->getSingleScalarResult();

        $date_recup = new \DateTime('now');

        /*DATE SECTION*/
        $dateNow = new \DateTime();
        $month =$dateNow->format('m 00:00:00');
        $Year =$dateNow->format('d-m-Y 00:00:00');
        $Day =$dateNow->format('d 00:00:00');

        /* Nb Tickets Par An  */
        $ticketYear= $this->managerRegistry->getRepository(Ticket::class)->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('YEAR(a.DateRegister) >= ?1')
            ->setParameter(1, $Year)
            ->getQuery()
            ->getSingleScalarResult();

        /* Nb Tickets Par Mois  */
        $ticketMonth= $this->managerRegistry->getRepository(Ticket::class)->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('MONTH(a.DateRegister) >= ?1')
            ->setParameter(1, $month)
            ->getQuery()
            ->getSingleScalarResult();

        /* Nb Tickets Jour  */
        $ticketDay= $this->managerRegistry->getRepository(Ticket::class)->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('DAY(a.DateRegister) >= ?1')
            ->setParameter(1, $Day)
            ->getQuery()
            ->getSingleScalarResult();

        $statsCategorie = [ 1 => ['Imprimante Achat'=>10] , 2 => ['Imprimante Vente'=>10] , 3 =>['Cash Soft'=>10] ,4 => ['Maintenance PC'=>10], 5 => ['Inventaire'=>10] , 6 => ['Fidélité'=>10] ];
        $statsTech =[1,2,3,4];

        if ($searchForm->isSubmitted())
        {
            // recuperer magasin from base
            $MagasinChoice = $this->managerRegistry->getRepository(Magasin::class)->findBy(['id'=>$MagasinChoice->getId()],['Nom'=>$MagasinChoice->getNom(),array('Nom'=>'asc')]);

            /* Nb Tickets Categorie Imp Achat Cat: 1 */
            $ticketCat1= $this->managerRegistry->getRepository(Ticket::class)->createQueryBuilder('a')
                ->select('COUNT(a.id)')
                ->where('a.Categorie = 1')
                ->andWhere('a.Mag = ?1')
                ->setParameter(1, $MagasinChoice->getId())
                ->getQuery()
                ->getSingleScalarResult();

            /* Nb Tickets Categorie Imp Vente Cat: 2 */
            $ticketCat2= $this->managerRegistry->getRepository(Ticket::class)->createQueryBuilder('a')
                ->select('COUNT(a.id)')
                ->where('a.Categorie = 2')
                ->andWhere('a.Mag = ?1')
                ->setParameter(1, $MagasinChoice->getId())
                ->getQuery()
                ->getSingleScalarResult();

            /* Nb Tickets Categorie Cash Soft Cat: 4 */
            $ticketCat3= $this->managerRegistry->getRepository(Ticket::class)->createQueryBuilder('a')
                ->select('COUNT(a.id)')
                ->where('a.Categorie = 4')
                ->andWhere('a.Mag = ?1')
                ->setParameter(1, $MagasinChoice->getId())
                ->getQuery()
                ->getSingleScalarResult();

            /* Nb Tickets Categorie Maintenance pc Cat: 15 */
            $ticketCat4= $this->managerRegistry->getRepository(Ticket::class)->createQueryBuilder('a')
                ->select('COUNT(a.id)')
                ->where('a.Categorie = 15')
                ->andWhere('a.Mag = ?1')
                ->setParameter(1, $MagasinChoice->getId())
                ->getQuery()
                ->getSingleScalarResult();

            /* Nb Tickets Categorie Inventaire Cat: 26 */
            $ticketCat5= $this->managerRegistry->getRepository(Ticket::class)->createQueryBuilder('a')
                ->select('COUNT(a.id)')
                ->where('a.Categorie = 26')
                ->andWhere('a.Mag = ?1')
                ->setParameter(1, $MagasinChoice->getId())
                ->getQuery()
                ->getSingleScalarResult();

            /* Nb Tickets Categorie Fidélité Cat: 17  */
            $ticketCat6= $this->managerRegistry->getRepository(Ticket::class)->createQueryBuilder('a')
                ->select('COUNT(a.id)')
                ->where('a.Categorie = 17')
                ->andWhere('a.Mag = ?1')
                ->setParameter(1, $MagasinChoice->getId())
                ->getQuery()
                ->getSingleScalarResult();

            $statsCategorie = [ 1 => ['Imprimante Achat'=>$ticketCat1] , 2 => ['Imprimante Vente'=>$ticketCat2] , 3 =>['Cash Soft'=>$ticketCat3] ,4 => ['Maintenance PC'=>$ticketCat4], 5 => ['Inventaire'=>$ticketCat5] , 6 => ['Fidélité'=>$ticketCat6] ];

            /* Rapport des ticket resolues  */
            $tech1= $this->managerRegistry->getRepository(Ticket::class)->createQueryBuilder('a')
                ->select('COUNT(a.id)')
                ->where('a.Admin_id = 2')
                ->andWhere('a.Mag = ?1')
                ->setParameter(1, $MagasinChoice->getId())
                ->getQuery()
                ->getSingleScalarResult();

            $tech2= $this->managerRegistry->getRepository(Ticket::class)->createQueryBuilder('a')
                ->select('COUNT(a.id)')
                ->where('a.Admin = 3')
                ->andWhere('a.Mag = ?1')
                ->setParameter(1, $MagasinChoice->getId())
                ->getQuery()
                ->getSingleScalarResult();

            $tech3= $this->managerRegistry->getRepository(Ticket::class)->createQueryBuilder('a')
                ->select('COUNT(a.id)')
                ->where('a.Admin = 1')
                ->andWhere('a.Mag = ?1')
                ->setParameter(1, $MagasinChoice->getId())
                ->getQuery()
                ->getSingleScalarResult();

            $tech4= $this->managerRegistry->getRepository(Ticket::class)->createQueryBuilder('a')
                ->select('COUNT(a.id)')
                ->where('a.Admin = 4')
                ->andWhere('a.Mag = ?1')
                ->setParameter(1, $MagasinChoice->getId())
                ->getQuery()
                ->getSingleScalarResult();

            $statsTech =[$tech1,$tech2,$tech3,$tech4];
        }

        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
            'date_rec'=>$date_recup,
            'tNOK'=>$ticketsNOK,
            'tW' =>$ticketsW,
            'tOK'=>$ticketsOK,
            'TY'=>$ticketYear,
            'TM'=>$ticketMonth,
            'TD'=>$ticketDay,
            'MagChoice'=>$MagasinChoice,
            'StatsC'=>$statsCategorie,
            'StatsT'=>$statsTech,
            'satisfaction'=>$ticketsSatisfaction,
            'searchForm' => $searchForm->createView()
        ]);
    }


    // <------------------------- Page Admin Create.Catégories -------------------------->
    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    #[Route(path: 'admin/categories/create', name: 'create.categorie')]
    public function createCategorie(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $categorie = new Categorie();

        $form = $this->createForm(CategorieType::class, $categorie);

        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid() )
        {
            $entityManager = $this->managerRegistry->getManager();
            $entityManager->persist($categorie);
            $entityManager->flush();
            ECHO "YES";	
            return $this->redirectToRoute('admin.categorie.all');
        }

        return $this->render('admin/Admin_create_Categorie.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    
    // <------------------------- Page Admin Create.Information -------------------------->
    /**
     * @param Request $request
     * @return
     * @throws \Exception
     */
    #[Route(path: 'admin/informations/create', name: 'create.information')]
    public function createInformation(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $information = new Information();

        $form = $this->createForm(InformationType::class, $information);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() )
        {
            $information->setDateInsert(new \DateTime());
            $information->setDateUpdate(new \DateTime());
            $entityManager = $this->managerRegistry->getManager();
            $entityManager->persist($information);
            $entityManager->flush();

            return $this->redirectToRoute('admin.information.all');
        }

        return $this->render('admin/Admin_create_Information.html.twig', ['form' => $form->createView(),]);
    }


    //<-------------------------- Formulaire Création User ---------------------->
    #[Route(path: '/admin/user/new', name: 'admin.create.user')]
    public function NewUser(Request $request, UserPasswordHasherInterface $passwordHasher)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $User = new User();

        $form = $this->createFormBuilder($User)
            ->add('Nom', TextType::class, ['label' => 'Nom'])
            ->add('Prenom', TextType::class, ['label' => 'Prenom'])
            ->add('Login', TextType::class, ['label' => 'Identifiant'])
            ->add('Password', TextType::class, ['label' => 'Mot de passe'])
            ->add('magasin', EntityType::class, [ 'label'=> 'Magasin','class' => Magasin::class,
                    'choice_label' => 'nom','expanded' => false, 'multiple'=> true])
            ->add('Email', TextType::class, ['label' => 'Adresse Email'])
            ->add('Profil', EntityType::class, [ 'label'=> 'Categorie Utilisateur','class' => Profil::class,
                'choice_label' => 'profil','expanded' => false
            ])
            /*
                ,
                'preferred_choices' => function ($value) {
                // prefer options within 3 days
                        return "user";
            }


                */
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()&& $form->isValid())
        {
            $User->setBDel(0);
            $User->setDateRegister(new \DateTime());

            $encoded = $passwordHasher->hashPassword($User, $User->getPassword());

            $User->setPassword($encoded);

            $entityManager = $this->managerRegistry->getManager();
            $entityManager->persist($User);
            $entityManager->flush();

            return $this->redirectToRoute('admin.user.all');
        }

        return $this->render('admin/Admin_create_user.html.twig', [
            'controller_name' => 'AdminController',
            'form'=>$form->createView(),
            ]);
    }


    // <------------------------- Page Admin Delete.User -------------------------->
    /**
     * @param $id
     * @param Request $request
     */
    #[Route(path: 'admin/users/{id}/delete', name: 'admin.user.delete')]
    public function deleteUser( Request $request, $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $this->managerRegistry->getRepository(User::class)->find($id);

        //$message = $user->getMessage();

        $tickets = $this->managerRegistry->getRepository(Ticket::class)->findBy(array('User'=>$id));

        $entityManager = $this->managerRegistry->getManager();

        foreach ($tickets as $ticket) 
        {
            $entityManager->remove($ticket);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        if ($id == NULL)
        {
           return $this->redirectToRoute('admin.user.all');
        }

        return $this->redirectToRoute('admin.user.all');
    }


    // <------------------------- Page Admin Create.Magasins -------------------------->
    /**
     * @param Request $request
     * @return
     * @throws \Exception
     */
    #[Route(path: 'admin/magasins/create', name: 'magasin.create')]
    public function createMagasin(Request $request, MailerInterface $mailer)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $magasin = new Magasin();
        $form = $this->createForm(MagasinType::class, $magasin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() )
        {
            $entityManager = $this->managerRegistry->getManager();
            $entityManager->persist($magasin);
            $entityManager->flush();

            // envoye du mail
            /*$email =(new Email())
                ->from('bilal.salmi@siege.cashconverters.fr')
                ->to('bilal.salmi@siege.cashconverters.fr')
                //->cc('cc@example.com') en copie
                //->bcc('bcc@example.com') en copie cache
                //->replyTo('example@example.com') reponse
                ->priority(Email::PRIORITY_HIGH)
                ->subject('Support : nouveau Ticket')
                ->text('nouveau Ticket !')
                ->html('<p>email body: Creation d un ticket</p>');

            $sentEmail = $mailer->send($email);*/

            return $this->redirectToRoute('admin.mag.all');
        }

        // $this->addFlash('success','Magasin Crée !');

        return $this->render('admin/Admin_create_Magasin.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    protected function AjoutMessage(Ticket $idTicket, Message $message)
    {
        $message->setTicket($idTicket);
        $idTicket->addMessage($message);
        $message->setUser($this->getUser());
    }


    // <------------------------- Page Admin Create.Ticket -------------------------->
    /**
     * @param Request $request
     * @return
     * @throws \Exception
     */
    #[Route(path: 'admin/tickets/create', name: 'admin.create.ticket')]
    public function createTicket(Request $request, MailerInterface $mailer)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $ticket = new Ticket();
        $message = new Message();
        
        $ticket->getMessage()->add($message);

        $message->setTicket($ticket);

        $form = $this->createForm(TicketType::class, $ticket);
          
        $form->handleRequest($request);

        $status = $this->managerRegistry->getRepository(Status::class)->find(1);

        $avis = $this->managerRegistry->getRepository(Avis::class)->find(1);

        if($form->isSubmitted() && $form->isValid() )
        {
            $ChoixMag = $form->get("Mag")->getData();

            $ticket->setMag($ChoixMag);
            $ticket->setDateRegister(new \DateTime());
            $ticket->setStatus($status);
            $ticket->setUser($this->getUser());
            $ticket->setMessageNonLu(1);
            $ticket->setMessageNonLuAdmin(0);
            $ticket->setAvis($avis);

            $message->setUser($this->getUser());
            $message->setDateRegister(new \DateTime());

            // When an admin creates a new ticket we create a notification 
            $notification = new Notification();
            $notification->setTicket($ticket);
            $notification->setMagasin($ticket->getMag());
            $notification->setType('created');
            $notification->setCreatedAt(new \DateTimeImmutable());

            $entityManager = $this->managerRegistry->getManager();

            // This notification will be send to all admins except the admin who created the ticket
            // So we get the list of all admins
            $allAdmins = $this->managerRegistry->getRepository(User::class)->findBy(['Profil' => 1]);
            
            // We add a notification for admins except the admin who created the ticket
            foreach($allAdmins as $admin)
            {
                if($admin !== $this->getUser())
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
                ->from('noreply@siege.cashconverters.fr')
                ->to($ticket->getUser()->getEmail())
                ->cc('support@siege.cashconverters.fr')
                //->bcc('bcc@example.com') en copie cache
                ->priority(Email::PRIORITY_HIGH)
                ->subject(' Support Cash Converters : Ticket n° '.$ticket->getId().' est crée')
                ->html('<p> Bonjour '.$ticket->getUser()->getLogin().', <br><br> Votre ticket n°'.$ticket->getId().' a été créé avec succès.<br> Catégorie '.$ticket->GetCategorie().'<br> Message : '.$ticket->GetMessage()[0].'<br> <br> Votre ticket sera traité dans les meilleurs délai <br> Cordialement,<br> <br> Service Support,<br><b>Cash Converters</b></p>');

            $sentEmail = $mailer->send($email);
            

            /*$email =(new Email())
                ->from('bilal.salmi@siege.cashconverters.fr')
                ->to($ticket->getUser()->getEmail())
                //->cc('support@siege.cashconverters.fr')
                //->bcc('bcc@example.com') en copie cache
                ->priority(Email::PRIORITY_HIGH)
                ->subject(' Support Cash Converters : Ticket n° '.$ticket->getId().' est crée')
                ->html('<p> Bonjour '.$ticket->getUser()->getLogin().', <br><br> Votre ticket n°'.$ticket->getId().' a été créé avec succès.<br> Catégorie '.$ticket->GetCategorie().'<br> Message : '.$ticket->GetMessage()[0].'<br> <br> Cordialement,<br> <br> Service Support,<br><b>Cash Converters</b></p>');

            $sentEmail = $mailer->send($email);*/

            return $this->redirectToRoute('admin.ticket.show', ['id'=>$ticket->getId()]);
        }

        return $this->render('admin/Admin_create_ticket.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    // <------------------------- Page Admin Tickets View -------------------------->
    /**
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: 'admin/tickets/{id}', name: 'admin.ticket.show')]
    public function show($id, Request $request, UserRepository $userRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // We get current ticket by id
        $ticket = $this->managerRegistry->getRepository(Ticket::class)->find($id);
        $ticket->setMessageNonLuAdmin(0);
        
        $entityManager = $this->managerRegistry->getManager();
        $entityManager->persist($ticket);
        $entityManager->flush();

        $message = new Message();
        $form_message = $this->createForm(MessageType::class, $message);
        $form_message->handleRequest($request);

        if ($form_message->isSubmitted() && $form_message->isValid())
        {
            $message->setUser($this->getUser());
            $message->setDateRegister(new \DateTime());
            $message->setTicket($ticket);
            
            $ticket->getMessage()->add($message);
            $ticket->setMessageNonLuAdmin($ticket->getMessageNonLuAdmin() + 1);
            $ticket->setMessageNonLu($ticket->getMessageNonLu() + 1);
            $ticket->setDateUpdate(new \DateTime());
           
            // When an admin writes a new message on ticket we create a notification
            $notification = new Notification();
            $notification->setTicket($ticket);
            $notification->setMagasin($ticket->getMag());
            $notification->setType('message');
            $notification->setCreatedAt(new \DateTimeImmutable());

            // This notification will be send to user who created this ticket and to all admins except the admin who wrote new message
            // So, we get the user who created this ticket 
            $ticketOwner = $userRepository->findOneBy(['id' => $ticket->getUser()->getId()]);

            $entityManager = $this->managerRegistry->getManager();

            // We add a notification for user who created this ticket
            $notificationUser = new NotificationUser();
            $notificationUser->setNotification($notification);
            $notificationUser->setUser($ticketOwner);
            $notificationUser->setIsRead(false);
            $entityManager->persist($notificationUser);
           
            // We get the list of all admins
            $allAdmins = $this->managerRegistry->getRepository(User::class)->findBy(['Profil' => 1]);

            // We add a notification for admins except the admin who created the ticket 
            foreach($allAdmins as $admin)
            {
                if($admin !== $this->getUser())
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

            return $this->redirectToRoute('admin.ticket.show', ['id'=>$ticket->getId()]);
        }

        if (!$ticket) 
        {
            //return $this->render('Erreur');
            throw $this->createNotFoundException('Pas de Tickets Trouvés.' . $id);
        }

        // We get all messages related to current ticket
        $messages = $this->managerRegistry->getRepository(Message::class)->findBy(['Ticket' => $id]);
                
        return $this->render('admin/Admin_TicketView.html.twig', [
                        'ticket' => $ticket,
                        'messages'=> $messages,
                        'form_message' => $form_message->createView(),
                    ]);
    }


    // <------------------------- Page Admin Ticket.open -------------------------->
    // This method changes ticket status from 1 (En Attente d'Assignation) to 2 (En Traitement)
    /**
     * @param $id
     * @return Response
     * @throws \Exception
     */
    #[Route(path: 'admin/tickets/{id}/activer', name: 'admin.tickets.activer')]
    public function active_tickets($id, MailerInterface $mailer, UserRepository $userRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $ticket = $this->managerRegistry->getRepository(Ticket::class)->find($id);

        $admin = $this->getUser();

        $StatusOuvert = $this->managerRegistry->getRepository(Status::class)->find(2);

        $ticket->setStatus($StatusOuvert);
        $ticket->setAdmin($admin);
        $ticket->setMessageNonLu(0);

        // When an admin opens a newly created ticket it's status passes from 1 (En Attente d'Assignation) to 2 (En Traitement).
        // So, we create a status change notification.
        $notification = new Notification();
        $notification->setTicket($ticket);
        $notification->setMagasin($ticket->getMag());
        $notification->setType('modified');
        $notification->setCreatedAt(new \DateTimeImmutable());

        // This notification will be send to user who created this ticket and to all admins except the admin who opened the ticket
        // So, we get the user who created this ticket 
        $ticketOwner = $userRepository->findOneBy(['id' => $ticket->getUser()->getId()]);

        $entityManager = $this->managerRegistry->getManager();

        // We add a status change notification for user who created this ticket
        $notificationUser = new NotificationUser();
        $notificationUser->setNotification($notification);
        $notificationUser->setUser($ticketOwner);
        $notificationUser->setIsRead(false);
        $entityManager->persist($notificationUser);
    
        // We get the list of all admins
        $allAdmins = $this->managerRegistry->getRepository(User::class)->findBy(['Profil' => 1]);

        // We add a status change notification for all admins except the admin who opened the ticket 
        foreach($allAdmins as $admin)
        {
            if($admin !== $this->getUser())
            {
                $notificationUser = new NotificationUser();
                $notificationUser->setNotification($notification);
                $notificationUser->setUser($admin);
                $notificationUser->setIsRead(false);
                $entityManager->persist($notificationUser);
            }
        }

        $entityManager->persist($ticket);
        $entityManager->persist($notification);
        $entityManager->flush();

        $email =(new Email())
            ->from('noreply@siege.cashconverters.fr')
            ->to($ticket->getUser()->getEmail())
            ->cc('support@siege.cashconverters.fr')
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Support Cash Converters : Ticket n° '.$ticket->getId().' est ouvert !')
            ->html('<p> Bonjour '.$ticket->getUser()->getLogin().', <br><br> Votre ticket n°'.$ticket->getId().' est en cours de traitement. <br> <br> <b> <u> Catégorie : </u></b>'.$ticket->GetCategorie().'<br> <br> Cordialement,<br> <b>Support Cash Converters</b></p>');

        $sentEmail = $mailer->send($email);

        return $this->redirectToRoute('admin.ticket.show', ['id'=>$ticket->getId()]);
    }


    // <------------------------- Page Admin Ticket.rappel -------------------------->
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
        

    // <------------------------- Page Admin Ticket.Resolve -------------------------->
    /**
     * @param $id
     * @return Response
     * @throws \Exception
     */
    #[Route(path: 'admin/tickets/{id}/resolve', name: 'admin.tickets.resolve')]
    public function resolve_tickets($id, MailerInterface $mailer)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $ticket = $this->managerRegistry->getRepository(Ticket::class)->find($id);

        $admin = $this->getUser();

        $StatusResolve = $this->managerRegistry->getRepository(Status::class)->find(3);

        $ticket->setStatus($StatusResolve);
        $ticket->setAdmin($admin);

        $ticket->setDateResolve(new \DateTime());

        // en base de donnée

        $entityManager = $this->managerRegistry->getManager();
        $entityManager->persist($ticket);
        $entityManager->flush();

        $email = (new TemplatedEmail())
            ->from('noreply@siege.cashconverters.fr')
            ->to($ticket->getMag()->getEmail())
            ->subject(' Support Cash Converters : Ticket n° '.$ticket->getId().' est résolue !')
            ->htmlTemplate('emails/resolutionmail.html.twig')
            ->context([
                'author' => $ticket->getUser()->getEmail() ,
                'idticket' => $id,
                'categorie' =>$ticket->getCategorie()->getNom(),
            ]);
        
            $sentEmail = $mailer->send($email);

        // redirection vers show
        return $this->redirectToRoute('admin.ticket.show', ['id'=>$ticket->getId()]);
    }
    
    
    // <------------------------- Page Admin Ticket.Resolve -------------------------->
    /**
     * @param $id, $note
     * @return Response
     * @throws \Exception
     */
    #[Route(path: 'admin/tickets/{id}/{note}', name: 'admin.tickets.satisfaction')]
    public function satisfaction_tickets($id, $note)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
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


    // <------------------------- Page Admin Remerciements ------------------------->
    /**
     * @param $id
     * @return Response
     * @throws \Exception
     */
    #[Route(path: 'admin/tickets/{id}/close', name: 'admin.tickets.close')]
    public function close_tickets($id, MailerInterface $mailer)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $admin = $this->getUser();

        $ticket = $this->managerRegistry->getRepository(Ticket::class)->find($id);

        $StatusClos = $this->managerRegistry->getRepository(Status::class)->find(4);

        // mise a jour de l'admin qui le cloture
        $ticket->setAdmin($admin);
        $ticket->setStatus($StatusClos);

        $ticket->setDateClosed(new \DateTime());

        // Update base de donnee

        $entityManager = $this->managerRegistry->getManager();
        $entityManager->persist($ticket);
        $entityManager->flush();

        $email =(new Email())
            ->from('noreply@siege.cashconverters.fr')
            ->to($ticket->getUser()->getEmail())
            ->cc('support@siege.cashconverters.fr')
            //->bcc('bcc@example.com') en copie cache
            ->priority(Email::PRIORITY_HIGH)
            ->subject(' Support Cash Converters : Ticket n° '.$ticket->getId().' est Clos !')
            ->html('<p> Bonjour '.$ticket->getUser()->getLogin().', <br><br> Votre ticket n°'.$ticket->getId().' a été clos ! Vous ne pouvez plus envoyer de nouveau message.<br> <br> Cordialement,<br> <br><b> Support Cash Converters</b></p>');

        $sentEmail = $mailer->send($email);

        return $this->redirectToRoute('admin.ticket.show', ['id'=>$ticket->getId()]);
    }


    // <------------------------- Page Admin All.Tickets -------------------------->
    /**
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Response
     * @throws \Exception
     */
    #[Route(path: 'admin/tickets', name: 'admin.tickets.all')]
    public function getAll( Request $request, PaginatorInterface $paginator) :Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $tickets = $this->managerRegistry
            ->getRepository(Ticket::class)
            ->findByStatus();
            //->findAll();

        return $this->render('admin/Admin_Ticket_All.html.twig', [
            'current_menu'=>'Tickets',
            'tickets'=>$tickets
        ]);
    }

        
    // <------------------------- Page Admin delete.Tickets -------------------------->
    /**
     * @param $id
     * @param Request $request
     * @throws \Exception
     */
    #[Route(path: 'admin/tickets/{id}/delete', name: 'admin.ticket.delete')]
    public function deleteTicket( Request $request, $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $ticket = $this->managerRegistry
            ->getRepository(Ticket::class)
            ->find($id);

        $ticket->setUser(null);
        $ticket->setAdmin(null);
        $files = $ticket->getFichiersFiles();

        $entityManager = $this->managerRegistry->getManager();
        $entityManager->remove($ticket);
        $entityManager->flush();

        if($id == NULL)
        {
            return $this->redirectToRoute('admin.tickets.all');
        }
        // <------------------------- Rendu Page Admin All.Tickets.delete -------------------------->
        return $this->redirectToRoute('admin.tickets.all');
    }


    // <------------------------- Page Admin All.Categorie -------------------------->
    /**
     * @param Request $request
     * @return Response
     */
    #[Route(path: 'admin/categories', name: 'admin.categorie.all')]
    public function getAllCategorie( Request $request) :Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $categories = $this->managerRegistry
            ->getRepository(Categorie::class)
            ->findAll();

        // <------------------------- Rendu Page Admin All.Categorie -------------------------->
        return $this->render('admin/Admin_Categorie_All.html.twig', [
            'current_menu'=>'Catégories',
            'categories'=>$categories
        ] );
    }


    // <------------------------- Page Admin All.Information -------------------------->
    /**
     * @param Request $request
     * @return Response
     */
    #[Route(path: 'admin/informations', name: 'admin.information.all')]
    public function getAllInformation( Request $request) :Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $informations = $this->managerRegistry
            ->getRepository(Information::class)
            ->findAll();

        // <------------------------- Rendu Page Admin All.Information -------------------------->
        return $this->render('admin/Admin_Information_All.html.twig', [
            'current_menu'=>'Information',
            'informations'=>$informations
        ] );
    }


    // <------------------------- Page Admin delete.Information -------------------------->
    /**
     * @param $id
     * @param Request $request
     * @throws \Exception
     */
    #[Route(path: 'admin/informations/{id}/delete', name: 'admin.information.delete')]
    public function deleteInformation( Request $request, $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $information = $this->managerRegistry
            ->getRepository(Information::class)
            ->find($id);

        $entityManager = $this->managerRegistry->getManager();
        $entityManager->remove($information);
        $entityManager->flush();

        if($id == NULL)
        {
            return $this->redirectToRoute('admin.information.all');
        }

        return $this->redirectToRoute('admin.information.all');
    }


    // <------------------------- Page Admin delete.Categorie -------------------------->
    /**
     * @param $id
     * @param Request $request
     * @throws \Exception
     */
    #[Route(path: 'admin/categories/{id}/delete', name: 'admin.categorie.delete')]
    public function deleteCategorie( Request $request, $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $categorie = $this->managerRegistry
            ->getRepository(Categorie::class)
            ->find($id);

        $entityManager = $this->managerRegistry->getManager();
        $entityManager->remove($categorie);
        $entityManager->flush();

        if($id == NULL)
        {
            return $this->redirectToRoute('admin.categorie.all');
        }

        return $this->redirectToRoute('admin.categorie.all');
    }


    // <------------------------- Page Admin Stats.Tickets ---------------------------------->
    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    #[Route(path: 'admin/Stats/Tickets', name: 'admin.stats.ticket')]
    public function StatsTicket(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $mag = $this->managerRegistry
            ->getRepository(Magasin::class)
            ->findAll();

        $form = $this->createFormBuilder()
            ->add('DateStart',DateType::class,['label'=>'Date de Début'])
            ->add('DateFin',DateType::class,['label'=>'Date de Fin'])
            /*->add('Choix',ChoiceType::class,array('choices'=>array('Nb Total Journalier'=> 1,'Nb Total Mensuel'=>2, 'Nb Total Annuel'=>3),
                'expanded'=>true,
                'multiple'=>false,
                'label'=>false
            ))*/
            ->add('Mag', EntityType::class, [ 'label'=> 'Magasin','class' => Magasin::class,
                'choice_label' => 'nom','expanded' => false,  'multiple'=>false, 'mapped'=>false])
            ->add('Mags', CheckboxType::class,['label'=>'tous les magasins', 'required'=> false ])
            ->getForm();

        $form->handleRequest($request);

        $statsT=1;

        $choiceMags = $form->get("Mags")->getData();

        $stats=null;
        $dateNow = null;

        $em = $this->managerRegistry->getManager();

        if ($form->isSubmitted() && $form->isValid() ) 
        {
            $choiceMag = $form->get("Mag")->getData();
            $dateStart =  $form->get("DateStart")->getData();
            $dateFin =  $form->get("DateFin")->getData();

            $Date1 = $dateStart;
            $Date2 = $dateFin;

            $result= null;

            if ( $choiceMags == true)
            {
                $date1S = $Date1->format('Y-m-d H:i:s');
                $date2S = $Date2->format('Y-m-d H:i:s');

                $RAW_QUERY = 'SELECT magasin.nom, COUNT(*) AS counter FROM ticket
                            LEFT JOIN magasin ON ticket.mag_id = magasin.id
                            WHERE ticket.date_register 
                            BETWEEN 
                                :date1
                            AND
                                :date2
                            GROUP BY magasin.nom ORDER BY magasin.nom ASC
                            ';

                $statement = $em->getConnection()->prepare($RAW_QUERY);

                $statement->bindValue('date1', $date1S);
                $statement->bindValue('date2', $date2S);

                $statement->execute();

                $result = $statement->fetchAll();
            }
            else
            {
                $stats = $this->managerRegistry->getRepository(Ticket::class)->createQueryBuilder('a')
                    ->select('COUNT(a.id)')
                    ->where('a.Mag = :Magasin')
                    ->andWhere('a.DateRegister BETWEEN :Date1 AND :Date2')
                    ->setParameter('Magasin', $choiceMag->getId())
                    ->setParameter('Date1', $Date1)
                    ->setParameter('Date2', $Date2)
                    ->getQuery()
                    ->getSingleScalarResult();
            }

            $SDate1 = $Date1->format('d-m-Y');
            $SDate2 = $Date2->format('d-m-Y');
            $DiffDate = $Date2->diff($Date1)->format("%y année %m mois et %d jours");

            return $this->render('admin/Admin_Stats_Tickets.html.twig', [
                'ChoiceMag'=>$choiceMag,
                'stats'=>$stats,
                'Date1'=>$SDate1,
                'Date2'=>$SDate2,
                'DateDiff'=>$DiffDate,
                'stats2'=>$result,
                'form'=> $form->createView()
            ]);
        }
        
        return $this->render('admin/Admin_Stats_Tickets.html.twig', [
            //'ChoiceMag'=>$mag,
            //'stats'=>$stats,
            //'Date'=>$dateNow,
            'form'=> $form->createView()
        ]);
    }


    // <------------------------- Page Admin Stats.Categorie ---------------------------------->
    /**
     * @param Request $request
     * @throws \Exception
     */
    #[Route(path: 'admin/Stats/Categorie', name: 'admin.stats.categorie')]
    public function StatsCategorie(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $result=null;
        $choice=null;

        $dateNow = new \DateTime();
        $month =$dateNow->format('Y-m-01 00:00:00');
        $Year =$dateNow->format('Y-01-01 00:00:00');
        $Day =$dateNow->format('Y-m-d 00:00:00');

        $form = $this->createFormBuilder()
            ->add('choix',ChoiceType::class, [
                'choices' => [
                    'Jour' => 1,
                    'Mois' => 2,
                    'Année' => 3
                ],])
            ->add('Mag', EntityType::class, [ 'label'=> 'Magasin','class' => Magasin::class,
                'choice_label' => 'nom','expanded' => false,  'multiple'=>false, 'mapped'=>false])

            ->add('Mags', CheckboxType::class,['label'=>'tous les magasins', 'required'=> false ])

            ->getForm();

        $form->handleRequest($request);

        $em = $this->managerRegistry->getManager();

        $valeur = $request->get('form');

        if ($form->isSubmitted() && $form->isValid() )
        {
            $choice = null;

            switch ($valeur["choix"]) 
            {
                case 1:
                    $choice=$Day;$date=$dateNow->format('d-m-Y');
                    break;
                case 2:
                    $choice=$month;$date=$dateNow->format('01-m-Y');
                    break;
                case 3:
                    $choice=$Year;$date=$dateNow->format('01-01-Y');
                    break;
            }

            $choiceMag = $form->get("Mag")->getData();
            $choiceMags = $form->get("Mags")->getData();

            if ( $choiceMags == true)
            {
                $RAW_QUERY = 'SELECT categorie.nom, count(*) AS counter  FROM ticket LEFT JOIN categorie  ON ticket.categorie_id = categorie.id WHERE ticket.date_register > :date  GROUP BY categorie.nom ORDER BY counter DESC';

                $statement = $em->getConnection()->prepare($RAW_QUERY);

                $statement->bindValue('date', $choice);
            }
            else
            {
                $RAW_QUERY = 'SELECT categorie.nom, count(*) AS counter  FROM ticket
                LEFT JOIN categorie ON ticket.categorie_id = categorie.id
                LEFT JOIN magasin ON ticket.mag_id = magasin.id
                WHERE magasin.id = :idmag
                AND ticket.date_register > :date
                GROUP BY categorie.nom ORDER BY counter DESC';

                $statement = $em->getConnection()->prepare($RAW_QUERY);

                $statement->bindValue('idmag', $choiceMag->getId());

                $statement->bindValue('date', $choice);
            }

            //$RAW_QUERY = 'SELECT nom, count(*) AS counter  FROM ticket LEFT JOIN categorie  ON ticket.categorie_id = categorie.id WHERE ticket.date_register > :date  GROUP BY categorie.nom ORDER BY counter DESC';

            $statement->execute();

            $result = $statement->fetchAll();


            return $this->render('admin/Admin_Stats_Catégories.html.twig', [
                'result'=>$result,
                'Date'=>$date,
                'form'=>$form->createView()
            ]);

        }
        
        return $this->render('admin/Admin_Stats_Catégories.html.twig', [
                'result'=>$result,
                'Date'=>$choice,
                'form'=>$form->createView()
            ]);

    }


    // <------------------------- Page Admin All.User -------------------------->
    /**
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Response
     */
    #[Route(path: 'admin/users', name: 'admin.user.all')]
    public function getAllUser( Request $request) :Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $this->managerRegistry
            ->getRepository(User::class)
            ->findAll();

        return $this->render('admin/Admin_user_All.html.twig', [
            'current_menu'=>'Tickets',
            'users'=>$users
        ] );
    }


    // <------------------------- Rendu Page Admin show.Users -------------------------->
    /**
     * @param $id
     * @param Request $request
     * @return Response
     */
    #[Route(path: 'admin/users/{id}', name: 'admin.user.show')]
    public function getOneUser( $id , Request $request ) :Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $this->managerRegistry
            ->getRepository(User::class)
            ->find($id);

        $magasin = $user->getMagasin();

        $form = $this->createForm(UserType::class, $user)
            ->add('magasin', EntityType::class, [ 'label'=> 'Magasin','class' => Magasin::class,
        'choice_label' => 'nom','expanded' => true, 'multiple'=> true])

        //->add('',CheckboxType::class,['label'=>'Tout les Magasins'])
        ->add('Submit', SubmitType::class,['label'=>'Modifier']);

            /* ->add('magasin', EntityType::class, [ 'label'=> 'Magasin','class' => Magasin::class,
        'choice_label' => 'nom','expanded' => false, 'multiple'=> true])
        ->add('Nom', TextType::class, ['label' => 'Nom'])
        ->add('Prenom', TextType::class, ['label' => 'Prenom'])
        ->add('Login', TextType::class, ['label' => 'Identifiant'])
        //->add('Password', TextType::class, ['label' => 'Mot de passe'])

        ->add('Email', TextType::class, ['label' => 'Adresse Email'])
        ->add('Profil', EntityType::class, [ 'label'=> 'Categorie Utilisateur','class' => Profil::class,
            'choice_label' => 'profil','expanded' => false,'preferred_choices' => function ($value) {
                return "user";
            }]);*/

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $user->setBDel(false);
            $entityManager = $this->managerRegistry->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('admin.user.all');
        }

        return $this->render('admin/Admin_UserView.html.twig', [
            'current_menu'=>'Utilisateur',
            'user'=>$user,
            'form'=>$form->createView()
        ] );
    }


    // <------------------------- Page Admin All.Magasins -------------------------->
    /**
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Response
     */
    #[Route(path: 'admin/magasins', name: 'admin.mag.all')]
    public function getAllMag( Request $request, PaginatorInterface $paginator) :Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $Magasins = $this->managerRegistry
            ->getRepository(Magasin::class)
            ->findAll();

        return $this->render('admin/Admin_Magasin_All.html.twig', [
            'current_menu'=>'Magasins',
            'Magasins'=>$Magasins
        ] );
    }


    // <------------------------- Rendu Page Admin show.Magasin -------------------------->
    /**
     * @param $id
     * @param Request $request
     * @return Response
     */
    #[Route(path: 'admin/magasin/{id}', name: 'admin.magasin.show')]
    public function getOneMag( $id , Request $request ) :Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $mag = $this->managerRegistry
            ->getRepository(Magasin::class)
            ->find($id);

        $form = $this->createForm(MagasinType::class, $mag);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $entityManager = $this->managerRegistry->getManager();
            $entityManager->persist($mag);
            $entityManager->flush();

            // return $this->redirectToRoute('admin.user.all');
        }

        return $this->render('admin/Admin_MagView.html.twig', [
            'magasin'=>$mag,
            'form'=>$form->createView()

        ] );
    }


    // <------------------------- DELETE magasins -------------------------->
    /**
     * @param $id
     * @param Request $request
     */
    #[Route(path: 'admin/magasins/{id}/delete', name: 'admin.magasin.delete')]
    public function deleteMagasin( Request $request, $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $mag = $this->managerRegistry->getRepository(Magasin::class)->find($id);

        $entityManager = $this->managerRegistry->getManager();
        $entityManager->remove($mag);
        $entityManager->flush();

        if ($id == NULL)
        {
            return $this->redirectToRoute('admin.mag.all');
        }

        return $this->redirectToRoute('admin.mag.all');
    }


    public function getSatisfaction()
    {
        $rawSql = "select SUM(case a.avis_id when 1 then 1 else 0 end) as status_indeterminer, SUM(case a.avis_id when 2 then 1 else 0 end) as status_satisfait, SUM(case a.avis_id when 5 then 1 else 0 end) as status_insatisfait, SUM(case a.avis_id when 3 then 1 else 0 end) as status_neutre FROM ticket AS a";

        $connection = $this->managerRegistry->getManager()->getConnection();
        $stmt = $connection->executeQuery($rawSql);

        return $stmt->fetchAllAssociative();
    }


    
}
