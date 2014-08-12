<?php

namespace Btn\MediaBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Routing\RouterInterface;

class MediaForm extends AbstractType
{
    /**
     * @var string $actionRouteName
     */
    private $actionRouteName = 'btn_media_mediacontrol_media_upload';

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
            ->add('file', 'btn_media_type_file', array('mapped' => false))
            ->add('category')
            ->add('name')
            ->add('description', 'textarea')
            ->add('save', $options['data']->getId() ? 'btn_update' : 'btn_create')
        ;
    }

    /**
     *
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            //TODO: set data_class ??
            // 'data_class' => null,
            'action'     => $this->router->generate($this->getActionRouteName(), $this->getActionRouteParams()),
            'novalidate' => true
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['attr']['novalidate'] = 'novalidate';
    }

    /**
     * Return form name
     * @return string
     */
    public function getName()
    {
        return 'btn_media_form_media';
    }

    /**
     * Set form action route name
     * @param string $routeName
     */
    public function setActionRouteName($routeName)
    {
        $this->actionRouteName = $routeName;
    }

    /**
     * Get form action route name
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
