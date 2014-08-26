<?php

namespace Btn\MediaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Btn\BaseBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/media")
 */
class MediaController extends AbstractController
{
    /**
     * @Route("/category/{id}", name="btn_media_media_category")
     * @Template()
     **/
    public function categoryAction(Request $request)
    {
        $categoryId            = $request->get('id');
        $mediaCategoryProvider = $this->get('btn_media.provider.media_category');
        $category              = $mediaCategoryProvider->getRepository()->findOneById($categoryId);

        $data                 = $this->getListData($request, false, $category);
        $data['isPagination'] = true;
        $data['isCategory']   = true;
        $data['category']     = $category;

        $template = $this->container->getParameter('btn_media.media_category.template');

        return $this->render($template, $data);
    }

    protected function getListData($request, $all = false, $category = null)
    {
        $mediaCategoryProvider = $this->get('btn_media.provider.media_category');
        $mediaProvider         = $this->get('btn_media.provider.media');

        $method     = ($all) ? 'findAll' : 'findByCategory';
        $categories = $mediaCategoryProvider->getRepository()->findAll();
        $entities   = $mediaProvider->getRepository()->$method($category);

        $params = $this->container->getParameter('btn_media');

        return array('categories' => $categories, 'entities' => $entities);
    }

    /**
     * @Route("/download/{id}", name="btn_media_media_download")
     * @Template()
     **/
    public function downloadAction(Request $request, $id)
    {
        $mediaProvider = $this->get('btn_media.provider.media');
        $entity        = $mediaProvider->getRepository()->findOneById($id);

        // Generate response
        if ($entity) {
            $response = new Response();
            $filename = $entity->getMediaPath();

            if (!file_exists($filename)) {
                throw $this->createNotFoundException('The file ' . $entity->getName() . ' does not exist.');
            }

            // Set headers
            $response->headers->set('Cache-Control', 'private');
            $response->headers->set('Content-type', mime_content_type($filename));
            $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($entity->getName()) . '"');
            $response->headers->set('Content-length', filesize($filename));

            // Send headers before outputting anything
            $response->sendHeaders();

            $response->setContent(readfile($filename));

            return $response;
        }

        throw $this->createNotFoundException('The entity does not exist');
    }
}
