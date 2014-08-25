<?php
namespace Btn\MediaBundle\Twig;

class MediaExtension extends \Twig_Extension
{

    public function __construct($manager)
    {
        $this->manager = $manager;
    }

    public function getFunctions()
    {
        return array(
            'btn_get_media' => new \Twig_Function_Method($this, 'getMedia'),
        );
    }

    public function getMedia($name)
    {
        return $this->manager->getMedia($name);
    }

    public function getName()
    {
        return 'btn.media.media_extensions';
    }
}
