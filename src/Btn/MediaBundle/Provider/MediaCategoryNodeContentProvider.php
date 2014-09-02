<?php

namespace Btn\MediaBundle\Provider;

use Btn\NodeBundle\Provider\NodeContentProviderInterface;
use Btn\MediaBundle\Form\NodeContentType;
use Btn\BaseBundle\Provider\EntityProviderInterface;

class MediaCategoryNodeContentProvider implements NodeContentProviderInterface
{
    /** @var \Btn\BaseBundle\Provider\EntityProviderInterface */
    protected $provider;

    /**
     *
     */
    public function __construct(EntityProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     *
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     *
     */
    public function getForm()
    {
        $medias = $this->provider->getRepository()->findAll();

        $data = array();
        foreach ($medias as $media) {
            $data[$media->getId()] = $media->getName();
        }

        return new NodeContentType($data);
    }

    /**
     *
     */
    public function resolveRoute($formData = array())
    {
        return 'btn_media_media_category';
    }

    /**
     *
     */
    public function resolveRouteParameters($formData = array())
    {
        return isset($formData['category']) ? array('id' => $formData['category']) : array();
    }

    /**
     *
     */
    public function resolveControlRoute($formData = array())
    {
        return 'btn_media_mediacontrol_media_index_category';
    }

    /**
     *
     */
    public function resolveControlRouteParameters($formData = array())
    {
        return isset($formData['category']) ? array('id' => $formData['category']) : array();
    }

    /**
     *
     */
    public function getName()
    {
        return 'btn_media.media_category_node_content_provider.name';
    }
}
