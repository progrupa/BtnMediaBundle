<?php

namespace Btn\MediaBundle\Form\Type;

use Btn\AdminBundle\Form\Type\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class MediaType extends AbstractType
{
    /** @var string $modalRouteName */
    private $modalRouteName = 'btn_media_mediacontrol_modal';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $this->assetLoader->load('btn_media_modal_js');
    }

    /**
     *
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'empty_value'   => 'btn_media.form.type.media.empty_value',
            'label'         => 'btn_media.form.type.media.label',
            'class'         => $this->entityProvider->getClass(),
            'attr'          => array(
                'btn-media'        => $this->router->generate($this->getModalRouteName()),
                'btn-media-select' => $this->trans('btn_media.form.type.media.select'),
                'btn-media-delete' => $this->trans('btn_media.form.type.media.remove')

                ),
            'query_builder' => function (EntityRepository $em) {
                return $em
                    ->createQueryBuilder('mf')
                    ->orderBy('mf.name', 'ASC')
                ;
            },
            'property' => 'name',
            'required' => true,
            'expanded' => false,
        ));
    }

    public function getParent()
    {
        return 'entity';
    }

    public function getName()
    {
        return 'btn_media';
    }

    /**
     * Get modal action route name
     * @param
     * @return string
     */
    public function getModalRouteName()
    {
        return $this->modalRouteName;
    }
}
