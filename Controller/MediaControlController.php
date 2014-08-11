<?php

namespace Btn\MediaBundle\Controller;

use Btn\MediaBundle\Model\MediaUploader;
use Gaufrette\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Btn\AdminBundle\Controller\AbstractControlController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Media controller.
 *
 * @Route("/media")
 */
class MediaControlController extends AbstractControlController
{
    /**
     * @Route("/", name="btn_media_mediacontrol_index")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $data                 = $this->getListData($request);
        $data['isPagination'] = true;

        return $data;
    }

    /**
     * Lists all Nodes.
     *
     * @Route("/tree", name="cp_nodes_tree")
     * @Template()
     */
    public function treeAction(Request $request)
    {
        $provider = $this->get('btn_media.provider.media_category');
        $repo     = $provider->getRepository();
        $current  = null;
        if ($request->get('category') !== null) {
            $current = $repo->find($request->get('category'));
        }

        return array('categories' => $repo->findAll(), 'currentNode' => $current);
    }

    /**
     * @Route("/new", name="btn_media_mediacontrol_new")
     * @Template("BtnMediaBundle:MediaControl:form.html.twig")
     */
    public function newAction(Request $request)
    {
        $form = $this->get('btn_media.adapter')->createForm();

        return array('form' => $form->createView(), 'entity' => null);
    }

    /**
     * @Route("/edit/{id}", name="btn_media_mediacontrol_media_edit")
     * @Template("BtnMediaBundle:MediaControl:form.html.twig")
     **/
    public function editAction(Request $request, $id)
    {
        $provider = $this->get('btn_media.provider.media');
        $entity   = $this->getRepository()->find($id);
        $adapter  = $this->get('btn_media.adapter');
        $form     = $adapter->createForm($request, $entity);

        return array('form' => $form->createView(), 'entity' => $entity);
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
    public function editXHRAction(Request $request)
    {
        $result = true;

        try {
            $mediaProvider         = $this->get('btn_media.provider.media');
            $mediaCategoryProvider = $this->get('btn_media.provider.media_category');


            $method = 'set' . ucfirst($type);
            $value  = $request->get('value');
            $entity = $mediaProvider->getRepository()->find($request->get('id'));

            if ($type == 'category') {
                $value = $mediaCategoryProvider->getRepository()->find($value);
            }

            $entity->$method($value);

            $em = $this->getManager();
            $em->persist($entity);
            $em->flush();
        } catch (\Exception $e) {
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
            $provider = $this->get('btn_media.provider.media');
            $entity = $provider->getRepository()->find($request->get('id'));
            $provider->delete($entity);
        } catch (\Exception $e) {
            $result = $e->getMessage();
        }

        return $this->json(array('result' => $result));
    }

    /**
     * @Route("/upload", name="btn_media_mediacontrol_upload")
     **/
    public function uploadAction(Request $request)
    {
        $categoryId = $request->get('categoryId', null);
        $mediaCategoryProvider = $this->get('btn_media.provider.media_category');
        $category   = $categoryId ? $mediaCategoryProvider->getRepository()->find($categoryId) : null;


        /** @var MediaUploader $uploader */
        $uploader = $this->get('mediafile.uploader');
        $uploader->setCategory($category);
        $uploader->setFilesystem($filesystem);
        // $uploader->handleUpload($uploadedFile);
        $adapter = $this->get('btn_media.adapter');
        $adapter->createForm($request);
        $uploader->setAdapter($adapter);

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
        $mediaCategoryProvider = $this->get('btn_media.provider.media_category');
        $category   = $mediaCategoryProvider->getRepository()->findOneById($categoryId);

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
        $result                = true;
        $mediaCategoryProvider = $this->get('btn_media.provider.media_category');
        $mediaCategory         = $mediaCategoryProvider->create();
        $mediaCategory->setName($request->get('name'));

        try {
            $mediaCategoryProvider->save($mediaCategory);
        } catch (\Exception $e) {
            $result = $e->getMassage();
        }

        return $this->json(array('result' => $result));
    }

    /**
     * @Route("/category-delete/{id}", name="btn_media_mediacontrol_categorydelete")
     **/
    public function categoryDeleteAction(Request $request, $id)
    {
        $mediaCategoryProvider = $this->get('btn_media.provider.media_category');
        $category = $mediaCategoryProvider->getRepository()->find($id);

        if ($category) {
            $mediaCategoryProvider->delete($category);
        }

        return $this->redirect($this->generateUrl('btn_media_mediacontrol_category'));
    }

    /**
     * @Route("/media-category/new", name="btn_media_mediacontrol_new_category")
     * @Template("BtnMediaBundle:MediaControl:categoryForm.html.twig")
     **/
    public function newCategoryAction(Request $request)
    {
        $mediaCategoryProvider = $this->get('btn_media.provider.media_category');
        $entity = $mediaCategoryProvider->create();
        $form   = $this->createForm('btn_media_form_mediacategory', $entity);

        return array('form' => $form->createView());
    }

    /**
     * @Route("/media-category/create", name="btn_media_mediacontrol_create_category")
     * @Method({"POST"})
     * @Template("BtnMediaBundle:MediaControl:categoryForm.html.twig")
     **/
    public function createCategoryAction(Request $request)
    {
        $mediaCategoryProvider = $this->get('btn_media.provider.media_category');
        $entity = $mediaCategoryProvider->create();
        $form   = $this->createForm('btn_media_form_mediacategory', $entity);
        $form->handleRequest($request);
        $category = $form->getData();
        //save MediaCategory entity
        $mediaCategoryProvider->save($entity);

        return $this->redirect($this->generateUrl('btn_media_mediacontrol_edit_category', array('id' => $category->getId())));
    }

    /**
     * @Route("/media-category/edit/{id}", name="btn_media_mediacontrol_edit_category")
     * @Template("BtnMediaBundle:MediaControl:categoryForm.html.twig")
     **/
    public function editCategoryAction(Request $request, $id)
    {
        $mediaCategoryProvider = $this->get('btn_media.provider.media_category');
        $entity = $mediaCategoryProvider->getRepository()->find($id);
        $form   = $this->createForm('btn_media_form_mediacategory', $entity);
        $form->handleRequest($request);
        //save MediaCategory entity
        $mediaCategoryProvider->save($entity);

        return array('form' => $form->createView());
        // return $this->redirect($this->generateUrl('btn_media_mediacontrol_category'));
    }

    /**
     * @Route("/media-category/update/{id}", name="btn_media_mediacontrol_update_category")
     * @Method({"POST"})
     * @Template("BtnMediaBundle:MediaControl:categoryForm.html.twig")
     **/
    public function updateCategoryAction(Request $request, $id)
    {
        $mediaCategoryProvider = $this->get('btn_media.provider.media_category');
        $entity = $mediaCategoryProvider->getRepository()->find($id);
        $form   = $this->createForm('btn_media_form_mediacategory', $entity);
        $form->handleRequest($request);
        //save MediaCategory entity
        $mediaCategoryProvider->save($entity);

        return $this->redirect($this->generateUrl('btn_media_mediacontrol_edit_category', array('id' => $category->getId())));
    }

    private function getListData($request, $all = false, $category = null)
    {
        if ($category == NULL) {
            $category = $request->get('category');
        }

        $mediaProvider         = $this->get('btn_media.provider.media');
        $mediaCategoryProvider = $this->get('btn_media.provider.media_category');

        $method     = $all ? 'findAll' : 'findByCategory';
        $categories = $mediaCategoryProvider->getRepository()->findAll();
        $entities   = $mediaProvider->getRepository()->$method($category);

        /* @todo: number of mediafiles per page - to bundle config */
        $pagination = $this->get('knp_paginator')->paginate($entities, $request->get('page', 1), 6);

        $allowedExtensions = $this->container->getParameter('btn_media.media.allowed_extensions');

        return array(
            'categories'         => $categories,
            'pagination'         => $pagination,
            'allowed_extensions' => $allowedExtensions,
        );
    }
}
