<?php

namespace Btn\MediaBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Routing\RouterInterface;

class MediaCategoryForm extends AbstractType
{
    /**
     * @var string $actionRouteName
     */
    private $actionRouteName = 'btn_media_mediacontrol_create_category';

    /**
     * @var string $actionRouteParams
     */
    private $actionRouteParams = array();

    /**
     *  @var RouterInterface $router
     */
    private $router;

    /**
     *
     */
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
            ->add('name')
            ->add('save', $options['data']->getId() ? 'btn_update' : 'btn_create');
        ;
    }

    /**
     *
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Btn\\MediaBundle\\Entity\\AbstractMediaCategory',
            'action'     => $this->router->generate($this->getActionRouteName(), $this->getActionRouteParams())
        ));
    }

    public function getName()
    {
        return 'btn_media_form_mediacategory';
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

    /**
     * Set form action route params
     * @param array $routeParams
     */
    public function setActionRouteParams($routeParams)
    {
        $this->actionRouteParams = $routeParams;
    }

    /**
     * Get form action route params
     * @return array $routeParams
     */
    public function getActionRouteParams()
    {
        return $this->actionRouteParams;
    }
}
