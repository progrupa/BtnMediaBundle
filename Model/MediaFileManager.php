<?php

namespace Btn\MediaBundle\Model;

use Doctrine\ORM\EntityManager;
use Knp\Component\Pager\Paginator;
use Symfony\Component\HttpFoundation\Request;
// use Btn\BaseBundle\Model\Manager;
use Btn\MediaBundle\Entity\MediaFile;

/**
 * Hero manager
 *
 **/
class MediaFileManager
{
    /**
     * Constructor.
     *
     * @param EntityManager   $em
     * @param Paginator       $paginator
     * @param Request         $request
     * @param Twig_Enviroment $twig
     * @param FormFsctory     $formFactory
     */
    public function __construct(EntityManager $em, Paginator $paginator, \Twig_Environment $twig, $formFactory, $container, $gaufretteMap)
    {
        // parent::__construct($em, $paginator, $twig, $formFactory);

        $this->em    = $em;
        $this->container    = $container;
        $this->gaufretteMap = $gaufretteMap;
        $this->repo         = $this->em->getRepository('BtnMediaBundle:MediaFile');
    }

    /**
     * Get images for node from hero repository
     *
     * @return array
     * @author
     **/
    public function getMediaFile($id)
    {
        $env    = $this->container->getParameter('kernel.environment');
        $file   = $this->repo->findOneById($id);

        if ($file != NULL) {
            if (FALSE && $env == 'dev') {
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
