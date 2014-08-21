<?php

namespace Btn\MediaBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MediaCategoryControlForm extends AbstractForm
{
    /** @var string $actionRouteName */
    protected $actionRouteName = 'btn_media_mediacontrol_create_category';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('name')
            ->add('save', $options['data']->getId() ? 'btn_update' : 'btn_create');
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'action' => $this->router->generate($this->getActionRouteName(), $this->getActionRouteParams())
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'btn_media_form_media_category_control';
    }
}
