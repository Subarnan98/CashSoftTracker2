<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Magasin;
use App\Entity\Ticket;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\All;


class TicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('Mag', EntityType::class, [
                        'label' => 'Magasins *',
                        'class' => Magasin::class,
                        'query_builder' => function (EntityRepository $er) {
                                return $er->createQueryBuilder('m')
                                ->orderBy('m.Nom', 'ASC');
                        },
                        'placeholder' => '-- Veuillez choisir un magasin --',  
                ])
                ->add('Categorie', EntityType::class, [
                        'label' => 'Catégorie *',
                        'class' => Categorie::class,
                        'query_builder' => function (EntityRepository $er) {
                                return $er->createQueryBuilder('c')
                                ->orderBy('c.Nom', 'ASC');
                        },
                        'choice_label' => 'nom',
                        'placeholder' => '-- Veuillez choisir une catégorie --',  
                ])
                ->add('Titre', TextType::class, ['label' => 'Titre *'])
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
                        'entry_options' => ['label' => false]
                ])
                ->add('IdTeamVw', TextType::class, [
                        'label' => 'Identifiant TeamViewer', 
                        'required'=>false
                ])
                ->add('CodeTeamWV', TextType::class, [
                        'label' => 'Mot de Passe TeamViewer', 
                        'required'=>false
                ]);
                // ->add('fichiers', FileType::class, [
                //     'label' => 'Ajouter des fichiers',
                //     'required' => false,
                //     'multiple' => true,
                //     'mapped' => false,
                //     'attr' => ['accept' => 'application/pdf, application/csv, text/plain, image/jpeg, image/jpg,, image/png'],
                //     'constraints' => [
                //         new All([
                //             'constraints' => [
                //               new File([
                //                 'maxSize' => '10M',
                //                 'maxSizeMessage' => 'La taille du fichier ne peut pas dépasser 10MB',
                //                 'mimeTypes' => [
                //                    'application/pdf',
                //                    'application/csv',
                //                    'text/plain',
                //                    'image/jpeg',
                //                    'image/jpg',
                //                    'image/png'
                //                  ],
                //                 'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF, PNG, JPEG, JPG, CSV ou texte valide',
                //               ])
                //             ],
                //         ])
                //     ]
                // ]);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
        ]);
    }
}