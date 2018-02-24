<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * DocumentType form.
 */
class DocumentType extends AbstractType
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
    {        $builder->add('title', null, array(
            'label' => 'Title',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('path', null, array(
            'label' => 'Path',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('summary', null, array(
            'label' => 'Summary',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('content', null, array(
            'label' => 'Content',
            'required' => true,
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
            'data_class' => 'AppBundle\Entity\Document'
        ));
    }

}
