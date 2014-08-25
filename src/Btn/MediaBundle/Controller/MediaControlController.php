<?php

namespace Btn\MediaBundle\Controller;

use Btn\MediaBundle\Model\MediaUploader;
use Gaufrette\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Btn\AdminBundle\Controller\AbstractControlController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Btn\AdminBundle\Annotation\EntityProvider;

/**
 * @Route("/media")
 * @EntityProvider()
 */
class MediaControlController extends AbstractControlController
{
    /**
     * @Route("/", name="btn_media_mediacontrol_media_index")
     * @Route("/category/{category}", name="btn_media_mediacontrol_media_index_category", requirements={"category" = "\d+"})
     * @Template()
     */
    public function indexAction(Request $request, $category = null)
    {
        return $this->getListData($request);
    }

    /**
     * @Route("/new", name="btn_media_mediacontrol_media_new")
     * @Route("/new/category/{category}", name="btn_media_mediacontrol_media_new_category", requirements={"category" = "\d+"})
     * @Template("BtnMediaBundle:MediaControl:form.html.twig")
     */
    public function newAction(Request $request, $category = null)
    {
        $form = $this->get('btn_media.adapter')->createForm($request);

        return array('form' => $form->createView(), 'entity' => null);
    }

    /**
     * @Route("/edit/{id}", name="btn_media_mediacontrol_media_edit", requirements={"id" = "\d+"})
     * @Template("BtnMediaBundle:MediaControl:form.html.twig")
     **/
    public function editAction(Request $request, $id)
    {
        $entity   = $this->getEntityProvider()->getRepository()->find($id);
        $form     = $this->get('btn_media.adapter')->createForm($request, $entity);

        return array('form' => $form->createView(), 'entity' => $entity);
    }

    /**
     * @Route("/upload/{id}", name="btn_media_mediacontrol_media_upload", requirements={"id" = "\d+"})
     * @Template("BtnMediaBundle:MediaControl:form.html.twig")
     **/
    public function uploadAction(Request $request, $id = null)
    {
        /** @var Media $entity */
        $entity = $id ? $this->getEntityProvider()->getRepository()->find($id) : null;
        /** @var Gaufrette/Filesystem $entity */
        $filesystem = $this->get('knp_gaufrette.filesystem_map')->get('btn_media');
        /** @var \Btn\MediaBundle\AdapterInterface $adapter */
        $adapter = $this->get('btn_media.adapter');
        $form    = $adapter->createForm($request, $entity);
        /** @var MediaUploader $uploader */
        $uploader = $this->get('btn_media.uploader');
        $uploader->setFilesystem($filesystem);
        $uploader->setAdapter($adapter);

        if ($request->isXmlHttpRequest()) {
            return $this->json(array(
                'success' => $uploader->isSuccess()
            ));
        } else {
            $medias = $uploader->getUploadedMedias();
            if (count($medias) > 0) {
                $id = $id ? $id : array_pop($medias)->getId();

                return $this->redirect($this->generateUrl('btn_media_mediacontrol_media_edit', array('id' => $id)));
            }
        }

        return array('form' => $form->createView(), 'entity' => null);
    }

    /**
     * @Route("/delete/{id}/{csrf_token}", name="btn_media_mediacontrol_media_delete", requirements={"id" = "\d+"})
     **/
    public function deleteAction(Request $request, $id, $csrf_token)
    {
        $this->validateCsrfTokenOrThrowException('btn_media_mediacontrol_media_delete', $csrf_token);

        $params = array();
        try {
            $provider = $this->getEntityProvider();
            $entity   = $provider->getRepository()->find($id);
            if ($entity->getCategory()) {
                $params['category'] = $entity->getCategory()->getId();
            }
            $provider->delete($entity);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());

        }

        return $this->redirect($this->generateUrl(empty($params) ? 'btn_media_mediacontrol_media_index' : 'btn_media_mediacontrol_media_index_category', $params));
    }

    /**
     * @Route("/modal", name="btn_media_mediacontrol_modal")
     * @Template("BtnMediaBundle:MediaModal:modal.html.twig")
     **/
    public function modalAction(Request $request)
    {
        $data = $this->getListData($request);
        if ($request->get('CKEditor')) {
            return $this->render('BtnMediaBundle:MediaModal:cke.html.twig', $data);
        }

        return $data;
    }

    /**
     * @Route("/modal-content", name="btn_media_mediacontrol_modalcontent")
     * @Route("/modal-content/{category}", name="btn_media_mediacontrol_modalcontent_category", requirements={"id" = "\d+"})
     * @Template("BtnMediaBundle:MediaModal:_content.html.twig")
     **/
    public function modalContentAction(Request $request)
    {
        return $this->getListData($request);
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
     * Get paginated media list
     */
    private function getListData(Request $request)
    {
        $category      = $request->get('category');
        $mediaProvider = $this->getEntityProvider();
        $method        = $category ? 'findByCategory' : 'findAll';
        $entities      = $mediaProvider->getRepository()->$method($category);

        /* @todo: number of mediafiles per page - to bundle config */
        $pagination = $this->get('knp_paginator')->paginate($entities, $request->get('page', 1), 6);

        return array('pagination' => $pagination);
    }
}
