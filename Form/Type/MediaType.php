<?php

namespace Btn\MediaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Btn\AdminBundle\Provider\EntityProviderInterface;

class MediaType extends AbstractType
{
    /** @var \Btn\AdminBundle\Provider\EntityProviderInterface */
    protected $provider;

    public function __construct(EntityProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'empty_value'   => 'btn_media.type.media.empty_value',
            'label'         => 'btn_media.type.media.label',
            'class'         => $this->provider->getClass(),
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
        return 'btn_media';
    }
}
