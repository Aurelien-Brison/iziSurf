<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Level;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class ProfileType extends AbstractType
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
                'label' => 'Téléphone'
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email *'
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe *'
            ])
            ->add('confirmPassword', PasswordType::class, [
                'label' => 'Confirme ton mot de passe *'
            ])
            ->add('level', EntityType::class, [
                'label' => 'Niveau en surf *',
                'class' => Level::class
            ])
            ->add('imageFile', FileType::class, [
                'label'     => 'Télécharge ta photo de profil',
                'required'  => true
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

// namespace App\Form;

// use App\Entity\Car;
// use Symfony\Component\Form\AbstractType;
// use Symfony\Component\Form\FormBuilderInterface;
// use Symfony\Component\OptionsResolver\OptionsResolver;
// use Symfony\Component\Form\Extension\Core\Type\TextType;
// use Symfony\Component\Form\Extension\Core\Type\CollectionType;

// class ProfilType extends AbstractType
// {
//     public function buildForm(FormBuilderInterface $builder, array $options)
//     {
//         $builder
//             ->add('brand', TextType::class)
//             ->add('model', TextType::class)
//             ->add('user', CollectionType::class,[
//             'entry_type' => UserType::class,
//             'entry_options' => [ 'label' => false ],
//             'allow_add' => true ,
//         ]
        
//         )
//         ;
//     }

//     public function configureOptions(OptionsResolver $resolver)
//     {
//         $resolver->setDefaults([
//             'data_class' => Car::class,
//         ]);
//     }
// }
