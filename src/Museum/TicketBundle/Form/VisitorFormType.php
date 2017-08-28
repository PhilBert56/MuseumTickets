<?php

namespace Museum\TicketBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\Date;

class VisitorFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)

    {
      $today = new \DateTime();
      $thisYear = $today->format('Y');

        $builder
            ->add('ticket', TicketType::class ,array('label' => 'Billet'))
            ->add('name',TextType::class,array('label' => 'Nom'))
            ->add('firstName',TextType::class,array('label' => 'Prénom'))
            ->add('birthDate', BirthdayType::class,array('label' => 'Date de naissance', 'years'=> range(1917,$thisYear)))
            ->add('country', CountryType::class,array('label' => 'Pays'))
            ->add('reducePrice', CheckboxType::class,  array('label' => 'Tarif Réduit', 'required' => false)    )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Museum\TicketBundle\Entity\Visitor'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'ticketbundle_visitor';
    }


}
