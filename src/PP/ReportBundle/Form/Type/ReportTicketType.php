<?php

namespace PP\ReportBundle\Form\Type;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ReportTicketType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder                   
            ->add('reason', 'entity', array(
                'class'    => 'PPReportBundle:ReportReason',
                'property' => 'name',
                'multiple' => false,                
                'label'=> false,
                'attr' => array(                                                                                
                    'class' => 'reason'
                )
            ))
            ->add('details',		'textarea', array(  
                'label'=> false,
                'attr' => array(                                                            
                    'placeholder' => 'More details...',
                    'class' => 'details',                     
            )))    
        ;               
		
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PP\ReportBundle\Entity\ReportTicket'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pp_reportbundle_report_ticket';
    }
}

