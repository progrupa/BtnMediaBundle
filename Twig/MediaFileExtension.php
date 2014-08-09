<?php
namespace Btn\MediaBundle\Twig;

class MediaFileExtension extends \Twig_Extension
{

    public function __construct($manager)
    {
        $this->manager = $manager;
    }

    public function getFunctions()
    {
        return array(
            'get_media_file' => new \Twig_Function_Method($this, 'getMediaFile'),
        );
    }

    public function getMediaFile($name)
    {
        return $this->manager->getMediaFile($name);
    }

    public function getName()
    {
        return 'mediafile_extensions';
    }
}
