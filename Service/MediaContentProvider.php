<?php

namespace Btn\MediaBundle\Service;

use Btn\NodesBundle\Service\NodeContentProviderInterface;
use Btn\MediaBundle\Form\NodeContentType;

/**
* MediaContentProvider
*
*/
class MediaContentProvider implements NodeContentProviderInterface
{

    private $router;
    private $em;

    public function __construct($router, $em)
    {
        $this->router = $router;
        $this->em     = $em;
    }

    public function getForm()
    {
        $medias = $this->em->getRepository('BtnMediaBundle:MediaFileCategory')->findAll();

        $data = array();
        foreach ($medias as $media) {
            $data[$media->getId()] = $media->getName();
        }

        return new NodeContentType($data);
    }

    public function resolveRoute($formData = array())
    {

        return 'app_media_category';
    }

    public function resolveRouteParameters($formData = array())
    {
        return array('id' => $formData['category']);
    }

    public function resolveControlRoute($formData = array())
    {

        return 'cp_media_category';
    }

    public function resolveControlRouteParameters($formData = array())
    {
        return array('id' => $formData['category']);
    }
}
