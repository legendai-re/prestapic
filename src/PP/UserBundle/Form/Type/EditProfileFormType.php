<?php

namespace PP\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use PP\ImageBundle\Form\Type\ImageType;

class EditProfileFormType extends AbstractType
{
   
     private $user;

    /**
     * @param string $class The User class name
     */
    public function __construct($user)
    {
        $this->user = $user;
    }
    
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name',           'text',array(
                'label'     => false,
                'required'  => false,
                'data'      => $this->user->getName()
                ))           
            ->add('profilImage',    new ImageType(),array(
                'label'     => false,
                'required'  => false,
                'data'      => $this->user->getProfilImage()
                ))
            ->add('coverImage',    new ImageType(),array(
                'label'     => false,
                'required'  => false,
                 'data'      => $this->user->getCoverImage()
                ))
            ->add('save', 		'submit', array(  
                'label'=> 'Envoyer',
                ))  
        ;
        
        $builder->addEventListener(
                FormEvents::POST_SUBMIT,    // 1er argument : L'évènement qui nous intéresse : ici, PRE_SET_DATA
                function(FormEvent $event) { // 2e argument : La fonction à exécuter lorsque l'évènement est déclenché
                        // On récupère notre objet Advert sous-jacent
                        $user = $event->getData();
                            
                        // Cette condition est importante, on en reparle plus loin
                        if (null === $user) {
                          return; // On sort de la fonction sans rien faire lorsque $advert vaut null
                        }

                        $profilImage = $user->getProfilImage();
                        $coverImage = $user->getCoverImage();
                        if($profilImage != null)$profilImage->setUploadDir("user/profile");                                                   
                        if($coverImage != null)$coverImage->setUploadDir("user/cover");
                }
        );
    }        

    /**
     * @return string
     */
    public function getName()
    {
        return 'pp_userbundle_profile_edit';
    }
}
