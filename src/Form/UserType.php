<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Level;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserType extends AbstractType
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
            ->add('age', IntegerType::class, [
                'label' => 'Âge *'
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'Genre *',
                'choices' => [
                    'Homme' => 'Masculin' ,
                    'Femme' => 'Féminin',]])
            ->add('phone', TextType::class, [
                'label' => 'Téléphone (optionnel)'
            ])
            ->add('level', EntityType::class, [
                'label' => 'Niveau en surf *',
                'class' => Level::class
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email *'
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe (8 caractères minimum) *',
                'always_empty' => false,
            ])
            ->add('confirmPassword', PasswordType::class, [
                'label' => 'Confirme ton mot de passe *',
                'always_empty' => false,
            ])
            ->add('imageFile', FileType::class, [
                'label'     => 'Télécharge ta photo de profil *',
                'required'  => true,
                'constraints' => new NotBlank(['message' => 'Télécharge une photo de profil'])
            ])
            ->add('leurre', HiddenType::class, [

            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'attr' => ['novalidate' => 'novalidate']
        ]);
    }
}
