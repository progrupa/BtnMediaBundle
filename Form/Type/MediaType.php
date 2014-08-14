<?php

namespace Btn\MediaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Btn\AdminBundle\Provider\EntityProviderInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class MediaType extends AbstractType
{
    /** @var \Btn\AdminBundle\Provider\EntityProviderInterface */
    protected $provider;
    /** @var \Symfony\Component\Routing\RouterInterface $router */
    private $router;
    /** @var \Symfony\Bundle\FrameworkBundle\Translation\TranslatorInterface $translator */
    private $translator;
    /** @var string $modalRouteName */
    private $modalRouteName = 'btn_media_mediacontrol_modal';

    public function __construct(EntityProviderInterface $provider, RouterInterface $router, TranslatorInterface $translator)
    {
        $this->provider   = $provider;
        $this->router     = $router;
        $this->translator = $translator;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'empty_value'   => 'btn_media.type.media.empty_value',
            'label'         => 'btn_media.type.media.label',
            'class'         => $this->provider->getClass(),
            'attr'          => array(
                'data-btn-media'        => $this->router->generate($this->getModalRouteName()),
                'data-btn-media-select' => $this->translator->trans('btn_media.media.select'),
                'data-btn-media-delete' => $this->translator->trans('btn_media.media.remove')

                ),
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
