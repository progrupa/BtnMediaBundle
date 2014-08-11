<?php

namespace Btn\MediaBundle\Manager;

use Doctrine\ORM\EntityManager;
use Knp\Component\Pager\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Btn\AdminBundle\Provider\EntityProviderInterface;

class MediaManager
{
    protected $environment;
    protected $gaufretteMap;
    protected $repo;

    /**
     *
     */
    public function __construct(EntityProviderInterface $provider, $environment, $gaufretteMap)
    {
        $this->environment  = $environment;
        $this->gaufretteMap = $gaufretteMap;
        $this->repo         = $provider->getRepository();
    }

    /**
     * Get images for node from hero repository
     *
     * @return array
     * @author
     **/
    public function getMedia($id)
    {
        $file = $this->repo->findOneById($id);

        if (null != $file ) {
            if (false && 'dev' === $this->environment) {
                return $file->getFile();
            } else {
                $filesystem = $this->gaufretteMap->get('btn_media');

                $map = \Gaufrette\StreamWrapper::getFilesystemMap();
                $map->set('media', $filesystem);

                \Gaufrette\StreamWrapper::register();

                return 'gaufrette://media/' . $file->getName();
            }
        }
    }
}
