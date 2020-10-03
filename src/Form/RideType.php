<?php

namespace App\Form;

use App\Entity\Car;
use App\Entity\Ride;
use App\Entity\Spot;
use App\Form\CarType;
use App\Repository\CarRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class RideType extends AbstractType
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
                'constraints' =>
                    new Callback([$this, 'cityDepartureValidation'])
            ])
            ->add('placeDeparture', TextType::class, [
                'label' => 'Lieu de rendez-vous *'
            ])
            ->add('spot', EntityType::class, [
                'label' => 'Spot d\'arrivée *',
                'placeholder' => 'Sélectionne un spot',
                'class' => Spot::class
            ])
            ->add('departureDate', DateType::class, [
                'label' => 'Date aller *',
                'widget' => 'single_text',
                'attr' => ['class' => 'js-datepicker'],
                'format'    => 'dd-MM-yyyy',
                'html5' => false
            ])
            ->add('departureHour', TimeType::class, [
                'label'         => 'Heure de départ *',
                'widget' => 'single_text',
                'attr' => ['class' => 'js-timepicker'],
                'html5' => false,
            ])
            ->add('returnDate', DateType::class, [
                'label' => 'Date retour',
                'widget' => 'single_text',
                'attr' => ['class' => 'js-datepicker'],
                'format'    => 'dd-MM-yyyy',
                'html5' => false,
                'constraints' => [
                new Callback([$this, 'returnValidation'])
            ]])
            ->add('returnHour', TimeType::class, [
                'label'         => 'Heure de retour',
                'widget' => 'single_text',
                'attr' => ['class' => 'js-timepicker'],
                'html5' => false,
            ])

            ->add('car', EntityType::class, [
                'label' => 'Voiture utilisée pour ce trajet *',
                'placeholder' => 'Sélectionne ou ajoute une voiture',
                'class' => Car::class,
                'query_builder' => function(CarRepository $carRepository) use ($user){
                    return $carRepository->findByUserId($user);
                },
                'constraints' => [
                    new Callback([$this, 'carValidation'])]              
            ])
            ->add('carAdd', CarType::class, array(
                'mapped'   => false,
                'label' => false,
            ))
            ->add('availableSeat', IntegerType::class, [
                'label' => 'Nombre de places assises dispo *'
            ])
            ->add('boardMax', IntegerType::class, [
                'label' => 'Nombre de planches que tu peux transporter *'
            ])
            ->add('boardSizeMax', NumberType::class, [
                'label' => 'Taille maximale des planches que tu peux transporter *',
                'invalid_message' => 'Format non valide : merci de séparer les décimales par un point ou une virgule',
            ])

            ->add('price', MoneyType::class, [
                'label' => 'Prix demandé par passager *',
                'invalid_message' => 'Format non valide : merci de ne saisir que des chiffres et de séparer les décimales par un point ou une virgule',
            ])
            ->add('rideDescription', TextType::class, [
                'label' => 'Précisions supplémentaires sur le trajet'
            ])
            ->add('cityLatitude', TextType::class, [
                'label' => false,
                'attr'  => ['style' => 'display: none;'],
            ])
            ->add('cityLongitude', TextType::class, [
                'label' => false,
                'attr'  => ['style' => 'display: none;'],
            ])
            ->add('optin', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'label' => "Je certifie être en possession d'un permis de conduire valide, d'un véhicule possédant un contrôle technique à jour et d'une assurance en cas d'accident.",
                'label_attr' => ['class' => 'checkbox__agreement'],
                'constraints' => 
                    new Callback([$this, 'optinValidation'])
            ])
            ;

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

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
                
            $data = $event->getData();
            $form = $event->getForm();

            if (empty($data['car']) && !empty($data['carAdd'])) {
                $form->remove('carAdd');

                $form->add('carAdd', CarType::class, array(
                    'property_path' => 'car',

                )); 
            } 

        });

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

    public function carValidation($object, ExecutionContextInterface $context, $payload)
    {
        $form = $context->getRoot();
        $data = $form->getData();
        $car = $data->getCar()->getId();

        if (!$car) {

            $brand = $form->get("carAdd")->get("brand")->getData();
            $model = $form->get("carAdd")->get("model")->getData();
    
            if ($brand == null || $model == null) {
    
                $context
                    ->buildViolation('Merci de sélectionner ou d\'ajouter une marque et un modèle de voiture')
                    ->addViolation();
            }
        }
    }

    public function optinValidation($object, ExecutionContextInterface $context, $payload)
    {
        $form = $context->getRoot();
        $optin = $form->get("optin")->getData();

        if ($optin == false) {
            $context
                ->buildViolation('Tu dois certifier avoir les documents demandés ci-dessus en ta possession pour proposer un trajet')
                ->addViolation();
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ride::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
        $resolver->setRequired(['user']);
    }

}
