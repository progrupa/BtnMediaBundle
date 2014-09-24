<?php

namespace Btn\MediaBundle\Form;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormInterface;

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
            ->add('file', 'btn_media_type_file', array(
                'mapped' => false,
            ))
            ->add('category', 'btn_media_category', array(
            ))
            ->add('name', null, array(
                'label' => 'btn_media.name.label',
            ))
            ->add('description', 'textarea', array(
                'label' => 'btn_media.description.label',
            ))
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
            'validation_groups' => function (FormInterface $form) {
                $bool = $form->get('file')->getData() == null ? null : $form->get('file')->getData()->isFile();
                if (!$bool) {
                    return array(Constraint::DEFAULT_GROUP, 'fileValidation');
                }

                return array(Constraint::DEFAULT_GROUP);
            },
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
