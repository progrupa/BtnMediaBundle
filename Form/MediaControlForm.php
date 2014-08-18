<?php

namespace Btn\MediaBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Routing\RouterInterface;

class MediaControlForm extends AbstractForm
{
    /** @var string $actionRouteName */
    protected $actionRouteName = 'btn_media_mediacontrol_media_upload';

    /**
     *
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('file', 'btn_media_type_file', array('mapped' => false))
            ->add('category', null, array(
                'label' => 'btn_media.category.label',
            ))
            ->add('name', null, array(
                'label' => 'btn_media.name.label',
            ))
            ->add('description', 'textarea', array(
                'label' => 'btn_media.description.label',
            ))
            ->add('save', $options['data']->getId() ? 'btn_update' : 'btn_create')
        ;
    }

    /**
     *
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'action' => $this->router->generate($this->getActionRouteName(), $this->getActionRouteParams()),
        ));
    }

    /**
     * Return form name
     * @return string
     */
    public function getName()
    {
        return 'btn_media_form_media_control';
    }
}
