<?php

namespace Btn\MediaBundle\Controller;

use Btn\MediaBundle\Model\MediaFileUploader;
use Gaufrette\Filesystem;
use Gaufrette\Adapter\Local as LocalAdapter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Btn\BaseBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Exception;

/**
 * News controller.
 *
 * @Route("/media")
 */
class MediaController extends BaseController
{
    /**
     * @Route("/category/{id}", name="app_media_category")
     * @Template()
     **/
    public function categoryAction(Request $request)
    {
        $categoryId = $request->get('id');
        $category   = $this->getRepository('BtnMediaBundle:MediaFileCategory')->findOneById($categoryId);

        $data                 = $this->getListData($request, FALSE, $category);
        $data['isPagination'] = TRUE;
        $data['isCategory']   = TRUE;
        $data['category']     = $category;

        $params = $this->container->getParameter('btn_media');

        return $this->render($params['template'], $data);
    }

    protected function getListData($request, $all = FALSE, $category = NULL)
    {
        $method     = ($all) ? 'findAll' : 'findByCategory';
        $categories = $this->getRepository('BtnMediaBundle:MediaFileCategory')->findAll();
        $entities   = $this->getRepository('BtnMediaBundle:MediaFile')->$method($category);

        $params = $this->container->getParameter('btn_media');

        return array('categories' => $categories, 'entities' => $entities);
    }

    /**
     * @Route("/download/{id}", name="app_media_download")
     * @Template()
     **/
    public function downloadAction(Request $request, $id) {
        $entity = $this->getRepository('BtnMediaBundle:MediaFile')->findOneById($id);;

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
            $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($entity->getName()) . ';"');
            $response->headers->set('Content-length', filesize($filename));

            // Send headers before outputting anything
            $response->sendHeaders();

            $response->setContent(readfile($filename));

            return $response;
        }

        throw $this->createNotFoundException('The entity does not exist');
    }

}
