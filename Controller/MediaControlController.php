<?php

namespace Btn\MediaBundle\Controller;

use Btn\MediaBundle\Model\MediaFileUploader;
use Gaufrette\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Btn\BaseBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Btn\MediaBundle\Entity\MediaFile;
use Btn\MediaBundle\Entity\MediaFileCategory;
use Exception;

/**
 * Media controller.
 *
 * @Route("/media")
 */
class MediaControlController extends BaseController
{
    /**
     * @Route("/", name="btn_media_mediacontrol_index")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $data                 = $this->getListData($request);
        $data['isPagination'] = true;

        $entity = new MediaFile();
        $form   = $this->createForm('btn_media_form_mediafile', $entity);

        $data['form'] = $form->createView();

        return $data;
    }
    /**
     * @Route("/list-modal", name="btn_media_mediacontrol_listmodal")
     * @Template()
     **/
    public function listModalAction(Request $request)
    {
        $separated = $request->get('separated');
        $data     = $this->getListData($request, true);

        $data['isModal']      = true;
        $data['isPagination'] = !$separated;
        $data['separated']    = $separated;

        return $data;
    }

    /**
     * @Route("/list-modal-content", name="btn_media_mediacontrol_listmodalcontent")
     * @Template("BtnMediaBundle::_list.html.twig")
     **/
    public function listModalContentAction(Request $request)
    {
        $category             = $request->get('category');
        $data                 = $this->getListData($request, ($category == NULL));
        $data['isModal']      = true;
        $data['isPagination'] = true;
        $data['separated']    = false;

        return $data;
    }

    /**
     * @Route("/edit", name="btn_media_mediacontrol_edit")
     * @Method({"POST"})
     **/
    public function editAction(Request $request)
    {
        $result = true;

        try {
            $type   = $request->get('type');
            $method = 'set' . ucfirst($type);
            $value  = $request->get('value');
            $entity = $this->getRepository('BtnMediaBundle:MediaFile')->find($request->get('id'));

            if ($type == 'category') {
                $value = $this->getRepository('BtnMediaBundle:MediaFileCategory')->find($value);
            }

            $entity->$method($value);

            $em = $this->getManager();
            $em->persist($entity);
            $em->flush();
        } catch (Exception $e) {
            $result = $e->getMessage();
        }

        return $this->json(array('result' => $result));
    }

    /**
     * @Route("/delete", name="btn_media_mediacontrol_delete")
     **/
    public function deleteAction(Request $request)
    {
        try {
            $result = true;
            $entity = $this->getRepository('BtnMediaBundle:MediaFile')->find($request->get('id'));

            $em = $this->getManager();
            $em->remove($entity);
            $em->flush();
        } catch (Exception $e) {
            $result = $e->getMessage();
        }

        return $this->json(array('result' => $result));
    }

    /**
     * @Route("/upload", name="btn_media_mediacontrol_upload")
     **/
    public function uploadAction(Request $request)
    {
        $entity = new MediaFile();
        $form   = $this->createForm('btn_media_form_mediafile', $entity);
        $form->handleRequest($request);
        $uploadedFile = $form->getData()->getFile() ? $form->getData()->getFile() : $request->files->get('file');//was qqfile
        // ldd($form->getData());
        $categoryId = $request->get('categoryId', null);
        $category   = $categoryId ? $this->getRepository('BtnMediaBundle:MediaFileCategory')->find($categoryId) : null;
        $filesystem = $this->get('knp_gaufrette.filesystem_map')->get('btn_media');

        /** @var MediaFileUploader $uploader */
        $uploader = $this->get('mediafile.uploader');
        $uploader->setCategory($category);
        $uploader->setFilesystem($filesystem);
        $uploader->handleUpload($uploadedFile);

        if ($request->isXmlHttpRequest()) {
            return $this->json(array(
                'success' => $uploader->isSuccess()
            ));
        } else {
            return $this->redirect($this->generateUrl('btn_media_mediacontrol_index'));
        }
    }

    /**
     * @Route("/dummy-upload", name="btn_media_mediacontrol_dummyupload")
     **/
    public function dummyUploadAction()
    {
        $filesystem = $this->get('knp_gaufrette.filesystem_map')->get('btn_media');

        $file = new \Gaufrette\File('text.txt', $filesystem);
        $file->setContent('Hello World');
        die();
    }

    /**
     * @Route("/category", name="btn_media_mediacontrol_category")
     * @Template("BtnMediaBundle:MediaControl:index.html.twig")
     **/
    public function categoryAction(Request $request)
    {
        $categoryId = $request->get('id');
        $category   = $this->getRepository('BtnMediaBundle:MediaFileCategory')->findOneById($categoryId);

        $data                 = $this->getListData($request, false, $category);
        $data['isPagination'] = true;
        $data['isCategory']   = true;
        $data['category']     = $category;

        return $data;
    }

    /**
     * @Route("/category-add", name="btn_media_mediacontrol_categoryadd")
     **/
    public function categoryAddAction(Request $request)
    {
        $result            = true;
        $mediaFileCategory = new MediaFileCategory();
        $mediaFileCategory->setName($request->get('name'));

        try {
            $em = $this->getManager();
            $em->persist($mediaFileCategory);
            $em->flush();
        } catch (Exception $e) {
            $result = $e->getMassage();
        }

        return $this->json(array('result' => $result));
    }

    /**
     * @Route("/category-delete/{id}", name="btn_media_mediacontrol_categorydelete")
     * @ParamConverter("category", class="BtnMediaBundle:MediaFileCategory")
     **/
    public function categoryDeleteAction(Request $request, $category)
    {
        if ($category) {
            $em = $this->getManager();
            $em->remove($category);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('btn_media_mediacontrol_category'));
    }

    private function getListData($request, $all = false, $category = null)
    {
        if ($category == NULL) {
            $category = $request->get('category');
        }

        $method     = ($all) ? 'findAll' : 'findByCategory';
        $categories = $this->getRepository('BtnMediaBundle:MediaFileCategory')->findAll();
        $entities   = $this->getRepository('BtnMediaBundle:MediaFile')->$method($category);

        /* @todo: number of mediafiles per page - to bundle config */
        $pagination = $this->get('knp_paginator')->paginate($entities, $request->get('page', 1), 6);
        // $pagination->setTemplate('BtnCrudBundle:Pagination:default.html.twig');

        $allowedExtensions = $this->container->getParameter('btn_media.allowed_extensions');

        return array('categories' => $categories, 'pagination' => $pagination, 'allowed_extensions' => $allowedExtensions);
    }
}
