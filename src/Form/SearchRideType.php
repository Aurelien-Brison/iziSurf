<?php

namespace App\Form;

use App\Entity\Spot;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class SearchRideType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $options['user'];

        $builder
            ->add('cityDeparture', SearchType::class, [
                'label' => 'Ville de départ *',
                'constraints' => [
                    new NotBlank(['message' => 'Renseigne ta ville de départ']),
                    new Callback([$this, 'cityDepartureValidation'])
            ]])
            ->add('spot', EntityType::class, [
                'label' => 'Spot d\'arrivée *',
                'placeholder' => 'Sélectionne un spot d\'arrivée',
                'constraints' => new NotBlank(['message' => 'Choisis un spot dans la liste']),
                'class' => Spot::class
            ])
            ->add('departureDate', DateType::class, [
                'label' => 'Date de l\'aller *',
                'widget' => 'single_text',
                'attr' => ['class' => 'js-datepicker'],
                'format'    => 'dd-MM-yyyy',
                'html5' => false,
                'constraints' => [new NotBlank(['message' => 'Renseigne ta date de départ']),
                new GreaterThan('yesterday')
            ]])
            ->add('returnDate', DateType::class, [
                'label' => 'Date du retour',
                'widget' => 'single_text',
                'attr' => ['class' => 'js-datepicker'],
                'format'    => 'dd-MM-yyyy',
                'html5' => false,
                'constraints' => [new GreaterThan('yesterday'),
                new Callback([$this, 'returnValidation'])
            ]])
            ->add('availableSeat', IntegerType::class, [
                'label' => 'Nombre de passagers *',
                'constraints' => [ new NotBlank(['message' => 'Renseigne le nombre de passagers']),
                new Range (['min' => 0, 'max' => 50])
            ]])
            ->add('boardMax', IntegerType::class, [
                'label' => 'Nombre de planches à transporter *',
                'constraints' => [ new NotBlank(['message' => 'Renseigne le nombre de planches à transporter']),
                new Range (['min' => 0, 'max' => 50]),
                new Callback([$this, 'boardValidation'])
            ]])
            ->add('boardSizeMax', NumberType::class, [
                'label' => 'Taille de la plus grande planche à transporter *',
                'invalid_message' => 'Format non valide : merci de séparer les décimales par un point ou une virgule',
                'constraints' => [ new NotBlank(['message' => 'Renseigne la taille de la plus grande planche']),
                new Range (['min' => 0, 'max' => 15])
            ]])
            ->add('cityLatitude', TextType::class, [
                'attr'  => ['style' => 'display: none;'],
                'label_attr'  => ['style' => 'display: none;']
            ])
            ->add('cityLongitude', TextType::class, [
                'attr'  => ['style' => 'display: none;'],
                'label_attr'  => ['style' => 'display: none;']
            ]);
            

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $user = $this->security->getUser();
            if (!$user) {
                throw new \LogicException(
                    'Vous devez être connecté pour continuer'
                );
            }

            $form = $event->getForm(); 
            if ($user->getGender() == 'Masculin') {
                $form->add('isSameGender', ChoiceType::class, [
                    'label' => 'Veux-tu voyager entre mecs seulement ? *',
                    'choices' => [
                        'Pas spécialement' => 0,
                        'Oui' => 1
                    ]
                ]);
            }
            if ($user->getGender() == 'Féminin')  {
                $form->add('isSameGender', ChoiceType::class, [
                    'label' => 'Veux-tu voyager entre nanas seulement ? *',
                    'choices' => [
                        'Pas spécialement' => 0,
                        'Oui' => 2
                    ]
                ]);
            }
        }
        );
    }

    public function cityDepartureValidation($object, ExecutionContextInterface $context, $payload)
    {
        $form = $context->getRoot();
        $data = $form->getData();

        if ($data->getCityDeparture() != null && $data->getCityLatitude() == null && $data->getCityLongitude() == null ) {
            $context
                ->buildViolation('Commence à taper un nom de ville et attend qu\'elle s\'autocomplète')
                ->addViolation();
        }
    }

    public function returnValidation($object, ExecutionContextInterface $context, $payload)
    {
        $form = $context->getRoot();
        $data = $form->getData();
        if ($data->getReturnDate() !== null) {

            if ($data->getDepartureDate() > $data->getReturnDate()) {
                $context
                    ->buildViolation('La date de retour ne peut pas être inférieure à la date d\'aller ;)')
                    ->addViolation();
            }
        }
    }

    public function boardValidation($object, ExecutionContextInterface $context, $payload)
    {
        $form = $context->getRoot();
        $data = $form->getData();

        if ($data->getBoardMax() > $data->getAvailableSeat()) {
            $context
                ->buildViolation('Pas plus d\'une planche par personne please')
                ->addViolation();
        }
    }



    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => false,
            'attr' => ['novalidate' => 'novalidate']
        ]);
        $resolver->setRequired(['user']);
    }
    
}
