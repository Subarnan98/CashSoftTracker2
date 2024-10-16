<?php

namespace App\Command;

use App\Entity\Ticket;
use App\Repository\StatusRepository;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use App\Repository\MagasinRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\MailingReminder;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DailyReminderMailSenderCommand extends Command
{
    protected static $defaultName = 'DailyReminderMailSender';

    private $MailingReminder;
    private $container;
    private $ticketRepository;
    private $userRepository;

    public function __construct(ContainerInterface $container, MagasinRepository $magRepository, TicketRepository $ticketRepository, MailerInterface $mailer)
    {
        $this->magRepository = $magRepository;
        $this->ticketRepository = $ticketRepository;
        $this->container = $container;
        $this->mailer = $mailer;

        parent::__construct();
    }
    protected function configure()
    {
        $this
            ->setDescription('Envoi des mails de rappels sur le stickets en traitements')
            ->setHelp('Cette commande permet d\'envoyer des mails de rappel automatique')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('Envoi des mails de Rappel !');

        $ticketRepo= $this->container->get('doctrine')->getManager();
        $magRepo= $this->container->get('doctrine')->getManager();
        //$mailer=$this->MailerInterface;

        $io->section('Récupération des Tickets');

        
        $tickets = $this->ticketRepository->findIdByTreatmentStatus();
      
        $io->progressStart(count($tickets));

        foreach ($tickets as $ticket)
        {
            $io->progressAdvance();
            $io->newLine(2);
            $io->section('Génération de Mails');
            $email = $this->ticketRepository->findOneEmailByTreatmentStatus($ticket);
            $mail =(new Email())
            ->from('noreply@siege.cashconverters.fr')
            ->to($email[0]['Email'])
            ->cc('support@siege.cashconverters.fr')
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Support Cash Converters : Ticket n° '.$ticket->getId().' est toujours en traitement !')
            ->html('<p> Bonjour '.$ticket->getUser()->getNom().', <br><br> Votre ticket n°'.$ticket->getId().' est toujours en cours de traitement. <br> <br> <b> <u> Catégorie : </u></b>'.$ticket->GetCategorie().'<br> <br> Cordialement,<br> <b>Support Cash Converters</b></p>');

            $this->mailer->send($mail);
            if (count($tickets) === 0) 
            {
                continue;
            }








            //$this->MailingReminder->rappelTickets($ticketRepo->getId(),$mailer);
    
            
        }
        $io->progressFinish();
    
        $io->success('Les Emails ont été envoyées.');

        // Code de base
         /* $arg1 = $input->getArgument('arg1');

            if ($arg1) 
            {
                $io->note(sprintf('You passed an argument: %s', $arg1));
            }

            if ($input->getOption('option1')) 
            {
                // ...
            }

            $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
            */
            return 0;
    }
}
