<?php

namespace PP\RequestBundle\Form\Type;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use PP\ImageBundle\Form\Type\ImageType;

class ImageRequestType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title',		'text', array(  
                'label'=> false,
                'attr' => array(                                                            
                    'placeholder' => 'Title of your request...',
                     'class' => 'title',                     
               )))
            ->add('request',            'textarea', array(  
                'label'=> false,
                'attr' => array(                                                            
                    'placeholder' => 'More information about what you\'d like...',
                    'class' => 'content init'
               )))
            ->add('category', 'entity', array(
                'class'    => 'PPRequestBundle:Category',
                'property' => 'name',
                'multiple' => false,                
                'label'=> false,
                'attr' => array(                                                                                
                    'class' => 'categories'
                )
              ))
            ->add('tagsStr',               'text', array(  
                'label'=> false,
                'attr' => array(                                                            
                    'placeholder' => 'Add tags... (seperate by commas) ',
                    'class' => 'add-tags'
               )))
            ->add('save', 		'submit', array(  
                'label'=> 'Envoyer',
                'attr' => array(                                                                                
                    'class' => 'button'
                )                
                ))                                    
        ;               
		
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PP\RequestBundle\Entity\ImageRequest'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pp_requestbundle_image_request';
    }
}

