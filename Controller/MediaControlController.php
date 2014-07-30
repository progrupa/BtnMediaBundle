<?php

namespace Btn\MediaBundle\Controller;

use Gaufrette\Filesystem;
use Gaufrette\Adapter\Local as LocalAdapter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Btn\BaseBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Btn\MediaBundle\Util\qqFileUploader;
use Btn\MediaBundle\Entity\MediaFile;
use Btn\MediaBundle\Entity\MediaFileCategory;

/**
 * News controller.
 *
 * @Route("/control/media")
 */
class MediaControlController extends BaseController
{
    /**
     * @Route("/", name="cp_media")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $data                   = $this->getListData($request);
        $data['isPagination']   = TRUE;

        return $data;
    }

    /**
     * @Route("/list-modal", name="cp_media_list_modal")
     * @Template()
     **/
    public function listModalAction(Request $request)
    {
        $separated          = $request->get('separated');
        $data               = $this->getListData($request, TRUE);

        $data['isModal']            = TRUE;
        $data['isPagination']       = ($separated != NULL) ? !$separated : TRUE;
        $data['expanded']           = ($separated != NULL) ? $separated : FALSE;

        return $data;
    }

    /**
     * @Route("/list-modal-content", name="cp_media_list_modal_content")
     * @Template("BtnMediaBundle::_list.html.twig")
     **/
    public function listModalContentAction(Request $request)
    {
        $category               = $request->get('category');
        $data                   = $this->getListData($request, ($category == NULL));
        $data['isModal']        = TRUE;
        $data['isPagination']   = TRUE;
        $data['expanded']       = FALSE;

        return $data;
    }

    /**
     * @Route("/edit", name="cp_media_edit")
     * @Method({"POST"})
     **/
    public function editAction(Request $request)
    {
        $result = TRUE;

        try{
            $type   = $request->get('type');
            $method = 'set' . ucfirst($type);
            $value  = $request->get('value');
            $entity = $this->getRepository('BtnMediaBundle:MediaFile')->findOneById($request->get('id'));

            if($type == 'category') {
                $value = ($value > 0) ?
                    $this->getRepository('BtnMediaBundle:MediaFileCategory')->findOneById($value) : NULL;
            }

            $entity->$method($value);

            $em = $this->getManager();
            $em->persist($entity);
            $em->flush();
        }
        catch(Exception $e) {
            $result = $e->getMessage();
        }

        return $this->json(array('result' => $result));
    }

    /**
     * @Route("/delete", name="cp_media_delete")
     **/
    public function deleteAction(Request $request)
    {
        try {
            $result = TRUE;
            $entity = $this->getRepository('BtnMediaBundle:MediaFile')->find($request->get('id'));

            $em = $this->getManager();
            $em->remove($entity);
            $em->flush();
        }
        catch(Exception $e) {
            $result = $e->getMessage();
        }

        return $this->json(array('result' => $result));
    }

    /**
     * @Route("/upload", name="cp_media_upload")
     **/
    public function uploadAction(Request $request)
    {
        $categoryId = $request->get('categoryId');
        $category   = $this->getRepository('BtnMediaBundle:MediaFileCategory')->findOneById($categoryId);

        $mediaFile  = new MediaFile();

        //upload using upload manager
        $uploader            = new qqFileUploader($this->container->getParameter('btn_media.allowed_extensions'));
        $uploader->sizeLimit = 1024 * 1024 * 1024;
        $result              = $uploader->handleUpload($mediaFile->getUploadRootDir());

        // To return a name used for uploaded file you can use the following line.
        $result['uploadName']   = $uploader->getUploadName();

        /*
        * 0. remove file when remove entity?
        * 1. remove file when uploading to s3?
        * 2. getUploadRootDir for
        *
        * 3. use this if prod:
                $adapter = new InMemoryAdapter(array('hello.txt' => 'Hello World!'));
                $filesystem = new Filesystem($adapter);

                $map = StreamWrapper::getFilesystemMap();
                $map->set('foo', $filesystem);

                StreamWrapper::register();

                echo file_get_contents('gaufrette://foo/hello.txt'); // Says "Hello World!"

        */

        if (isset($result['success'])) {
            $mediaFile->setName($result['uploadName']);
            $mediaFile->setFile($result['uploadName']);

            //add category if not null
            if ($category) {
                $mediaFile->setCategory($category);
            }

            /* @todo: add type detecting here */
            $em = $this->getManager();
            $em->persist($mediaFile);
            $em->flush();

            $filesystem = $this->get('knp_gaufrette.filesystem_map')->get('btn_media');

            if(!file_exists($mediaFile->getMediaPath())) {
                die($mediaFile->getMediaPath());
            }

            $file = new \Gaufrette\File($result['uploadName'], $filesystem);
            $file->setContent(file_get_contents($mediaFile->getMediaPath()));
        }

        return $this->json($result);
    }

    /**
     * @Route("/dummy-upload")
     **/
    public function dummyUploadAction()
    {
        $filesystem = $this->get('knp_gaufrette.filesystem_map')->get('btn_media');

        $file = new \Gaufrette\File('text.txt', $filesystem);
        $file->setContent('Hello World');
        die();
    }

    /**
     * @Route("/category", name="cp_media_category")
     * @Template("BtnMediaBundle:MediaControl:index.html.twig")
     **/
    public function categoryAction(Request $request)
    {
        $categoryId = $request->get('id');
        $category   = $this->getRepository('BtnMediaBundle:MediaFileCategory')->findOneById($categoryId);

        $data                   = $this->getListData($request, FALSE, $category);
        $data['isPagination']   = TRUE;
        $data['isCategory']     = TRUE;
        $data['category']       = $category;

        return $data;
    }

    /**
     * @Route("/category-add", name="cp_media_category_add")
     **/
    public function categoryAddAction(Request $request)
    {
        $result            = TRUE;
        $mediaFileCategory = new MediaFileCategory();
        $mediaFileCategory->setName($request->get('name'));

        try {
            $em = $this->getManager();
            $em->persist($mediaFileCategory);
            $em->flush();
        }
        catch(Exception $e) {
            $result = $e->getMassage();
        }

        return $this->json(array('result' => $result));
    }

    /**
     * @Route("/category-delete/{id}", name="cp_media_category_delete")
     * @ParamConverter("category", class="BtnMediaBundle:MediaFileCategory")
     **/
    public function categoryDeleteAction(Request $request, $category)
    {
        if ($category) {
            $em = $this->getManager();
            $em->remove($category);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('cp_media_category'));
    }

    private function getListData($request, $all = FALSE, $category = NULL)
    {
        if($category == NULL) {
            $category = $request->get('category');
        }

        $method     = ($all) ? 'findAll' : 'findByCategory';
        $categories = $this->getRepository('BtnMediaBundle:MediaFileCategory')->findAll();
        $entities   = $this->getRepository('BtnMediaBundle:MediaFile')->$method($category);

        /* @todo: number of mediafiles per page - to bundle config */
        $pagination = $this->get('knp_paginator')->paginate($entities, $request->get('page', 1), 6);
        $pagination->setTemplate('BtnCrudBundle:Pagination:default.html.twig');

        $allowedExtensions = $this->container->getParameter('btn_media.allowed_extensions');

        return array('categories' => $categories, 'pagination' => $pagination, 'allowed_extensions' => $allowedExtensions);
    }
}
