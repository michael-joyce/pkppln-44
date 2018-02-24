<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * DepositType form.
 */
class DepositType extends AbstractType
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
    {        $builder->add('journalVersion', null, array(
            'label' => 'Journal Version',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('license', null, array(
            'label' => 'License',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('fileType', null, array(
            'label' => 'File Type',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('depositUuid', null, array(
            'label' => 'Deposit Uuid',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('received', null, array(
            'label' => 'Received',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('action', null, array(
            'label' => 'Action',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('volume', null, array(
            'label' => 'Volume',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('issue', null, array(
            'label' => 'Issue',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('pubDate', null, array(
            'label' => 'Pub Date',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('checksumType', null, array(
            'label' => 'Checksum Type',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('checksumValue', null, array(
            'label' => 'Checksum Value',
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
                $builder->add('size', null, array(
            'label' => 'Size',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('state', null, array(
            'label' => 'State',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('errorLog', null, array(
            'label' => 'Error Log',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('plnState', null, array(
            'label' => 'Pln State',
            'required' => false,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('packageSize', null, array(
            'label' => 'Package Size',
            'required' => false,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('packagePath', null, array(
            'label' => 'Package Path',
            'required' => false,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('packageChecksumType', null, array(
            'label' => 'Package Checksum Type',
            'required' => false,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('packageChecksumValue', null, array(
            'label' => 'Package Checksum Value',
            'required' => false,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('depositDate', null, array(
            'label' => 'Deposit Date',
            'required' => false,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('depositReceipt', null, array(
            'label' => 'Deposit Receipt',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('processingLog', null, array(
            'label' => 'Processing Log',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                $builder->add('harvestAttempts', null, array(
            'label' => 'Harvest Attempts',
            'required' => true,
            'attr' => array(
                'help_block' => '',
            ),
        ));
                        $builder->add('journal');
                
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
            'data_class' => 'AppBundle\Entity\Deposit'
        ));
    }

}
