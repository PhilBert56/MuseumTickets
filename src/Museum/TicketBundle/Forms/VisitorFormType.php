<?php

namespace TicketBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class VisitorFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ticket', TicketType::class ,array('label' => 'Billet'))
            ->add('name',TextType::class,array('label' => 'Nom'))
            ->add('firstName',TextType::class,array('label' => 'Prénom'))
            ->add('birthDate',      BirthdayType::class,array('label' => 'Date de naissance'))
            ->add('country',      CountryType::class,array('label' => 'Pays'))
            ->add('reducePrice', CheckboxType::class,  array('label' => 'Tarif Réduit', 'required' => false)    )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TicketBundle\Entity\Visitor'
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
