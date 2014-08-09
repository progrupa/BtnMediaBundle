<?php

namespace Btn\MediaBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Routing\RouterInterface;

class MediaFileForm extends AbstractType
{
    /**
     * @var string $actionRouteName
     */
    private $actionRouteName = 'btn_media_mediacontrol_upload';

    /**
     *  @var RouterInterface $router
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }
    /**
     *
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', 'file', array(
                'label' => 'btn_media.form.file'
            ))
            // ->add('save', $options['data']->getId() ? 'btn_admin_update_button' : 'btn_admin_create_button');
            ->add('save', 'submit');
        ;
    }

    /**
     *
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            // 'data_class' => $this->manager->getProvider()->getContainerClass(),
            'action' => $this->router->generate($this->getActionRouteName())
        ));
    }

    public function getName()
    {
        return 'btn_media_form_mediafile';
    }

    /**
     * set form action route name
     * @param string $routeName
     */
    public function setActionRouteName($routeName)
    {
        $this->actionRouteName = $routeName;
    }

    /**
     * get form action route name
     * @param
     * @return string
     */
    public function getActionRouteName()
    {
        return $this->actionRouteName;
    }
}
