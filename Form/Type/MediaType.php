<?php

namespace Btn\MediaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Btn\AdminBundle\Provider\EntityProviderInterface;
use Symfony\Component\Routing\RouterInterface;

class MediaType extends AbstractType
{
    /** @var \Btn\AdminBundle\Provider\EntityProviderInterface */
    protected $provider;
    /** @var \Symfony\Component\Routing\RouterInterface $router */
    private $router;
    /** @var string $modalRouteName */
    private $modalRouteName = 'btn_media_mediacontrol_modal';

    public function __construct(EntityProviderInterface $provider, RouterInterface $router)
    {
        $this->provider = $provider;
        $this->router   = $router;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'empty_value'   => 'btn_media.type.media.empty_value',
            'label'         => 'btn_media.type.media.label',
            'class'         => $this->provider->getClass(),
            'attr'          => array('data-btn-media' => $this->router->generate($this->getModalRouteName())),
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
