<?php

namespace Btn\MediaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class MediaType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'empty_value'   => 'btn_media.type.media.empty_value',
            'label'         => 'btn_media.type.media.label',
            'class'         => 'BtnMediaBundle:MediaFile',
            'attr'          => array('class' => 'btn-media'),
            'query_builder' => function (EntityRepository $em) {
                return $em
                    ->createQueryBuilder('mf')
                    ->orderBy('mf.name', 'ASC');
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
        return 'btn_mediabundle_media';
    }
}
