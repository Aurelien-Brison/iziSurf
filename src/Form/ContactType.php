<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class   ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Prénom *'
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom *'
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email *'
            ])
            ->add('object', ChoiceType::class, [
                'choices' => [
                    'Demande d\'infos' => 'Information sur nos trajets',
                    'Problème technique'=> 'Problème technique',
                    'Autre demande' => 'Autre demande'
                ],
                'label' => 'Objet *'
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message *'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
            'label' => false,
            'attr' => ['novalidate' => 'novalidate']
        ]);
    }
}