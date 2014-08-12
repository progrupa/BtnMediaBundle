<?php

namespace Btn\MediaBundle\Service;

use Btn\NodesBundle\Provider\NodeContentProviderInterface;
use Btn\MediaBundle\Form\NodeContentType;
use Btn\AdminBundle\Provider\EntityProviderInterface;

class NodeContentProvider implements NodeContentProviderInterface
{
    private $provider;

    public function __construct(EntityProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    public function getForm()
    {
        $medias = $this->provider->getRepository()->findAll();

        $data = array();
        foreach ($medias as $media) {
            $data[$media->getId()] = $media->getName();
        }

        return new NodeContentType($data);
    }

    public function resolveRoute($formData = array())
    {
        return 'btn_media_media_category';
    }

    public function resolveRouteParameters($formData = array())
    {
        return array('id' => $formData['category']);
    }

    public function resolveControlRoute($formData = array())
    {
        return 'btn_media_mediacontrol_category';
    }

    public function resolveControlRouteParameters($formData = array())
    {
        return array('id' => $formData['category']);
    }
}
