<?php

namespace PP\PropositionBundle\Form\Type;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use PP\ImageBundle\Form\Type\ImageType;

class PropositionType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title',               "hidden",array(
                'data'=>"Proposition"
            ))
            ->add('image',		new ImageType(),array(                  
                'label'=> false)
            )           
        ;
        
         $builder->addEventListener(
                FormEvents::POST_SUBMIT,    // 1er argument : L'évènement qui nous intéresse : ici, PRE_SET_DATA
                function(FormEvent $event) { // 2e argument : La fonction à exécuter lorsque l'évènement est déclenché
                        // On récupère notre objet Advert sous-jacent
                        $proposition = $event->getData();
                            
                        // Cette condition est importante, on en reparle plus loin
                        if (null === $proposition) {
                          return; // On sort de la fonction sans rien faire lorsque $advert vaut null
                        }

                        $image = $proposition->getImage();
                        if($image != null)$image->setUploadDir("proposition");
                }
        );
		
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PP\PropositionBundle\Entity\Proposition'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pp_propositionbundle_proposition';
    }
    
    
}