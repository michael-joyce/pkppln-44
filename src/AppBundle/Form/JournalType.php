<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * JournalType form.
 */
class JournalType extends AbstractType
{
    /**
     * Add form fields to $builder.
     * 
     * @param FormBuilderInterface $builder
     *   The form builder to add the fields to.
     * @param array $options
     *   Options for the form, as defined in configureOptions.
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {        $builder->add('uuid', null, array(
            'label' => 'Uuid',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('contacted', null, array(
            'label' => 'Contacted',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('ojsVersion', null, array(
            'label' => 'Ojs Version',
            'required' => false,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('notified', null, array(
            'label' => 'Notified',
            'required' => false,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('title', null, array(
            'label' => 'Title',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('issn', null, array(
            'label' => 'Issn',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('url', null, array(
            'label' => 'Url',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('status', null, array(
            'label' => 'Status',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('termsAccepted', ChoiceType::class, array(
            'label' => 'Terms Accepted',
            'expanded' => true,
            'multiple' => false,
            'choices' => array(
                'Yes' => true,
                'No' => false,
                ),
            'required' => true,
            'placeholder' => false,
            'attr' => array(
                'help_block' => '',
            ),
            
        ));
                $builder->add('email', null, array(
            'label' => 'Email',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('publisherName', null, array(
            'label' => 'Publisher Name',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('publisherUrl', null, array(
            'label' => 'Publisher Url',
            'required' => false,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                
    }
    
    /**
     * Define options for the form.
     * 
     * Set default, optional, and required options passed to the 
     * buildForm() method via the $options parameter.
     *
     * @param OptionsResolver $resolver
     *   Resolver of options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Journal'
        ));
    }

}
