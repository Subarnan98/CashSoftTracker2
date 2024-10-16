<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Magasin;
use App\Entity\Message;
use App\Entity\Ticket;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\FormType;

use Symfony\Component\Validator\Constraints\File;


class TicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('Titre', TextType::class, ['label' => 'Titre *'])
            ->add('Mag', EntityType::class, [
                'label' => 'Magasins *',
                'class' => Magasin::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.Nom', 'ASC');
                }
            ])
            ->add('Categorie', EntityType::class, [
                'label' => 'Categorie *',
                'class' => Categorie::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.Nom', 'ASC');
                },
                'choice_label' => 'nom',
            ])
            ->add('nom', TextType::class, [
                'label'=>'Nom *','required'=>True,
                'attr'=>array('class'=>'text-uppercase')
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom *','required'=>True
            ])
            ->add('Message', CollectionType::class, [
                'label' => 'Message *',
                'entry_type' => MessageType::class,  'label'=>false,
                'entry_options' => ['label' => false]])

            ->add('IdTeamVw', TextType::class, [
                'label' => 'Identifiant TeamViewer', 
                'required'=>false
            ])
            ->add('CodeTeamWV', TextType::class, [
                'label' => 'Mot de Passe TeamViewer', 
                'required'=>false
            ])
            ->add('FichiersFiles', FileType::class, [
                'required'=>false,
                'multiple'=>true,
                'label' => 'Fichier(s)'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
        ]);
    }
}