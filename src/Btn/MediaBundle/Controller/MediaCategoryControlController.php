<?php

namespace Btn\MediaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Btn\AdminBundle\Controller\AbstractControlController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Btn\AdminBundle\Annotation\EntityProvider;

/**
 * @Route("/media/category")
 * @EntityProvider()
 */
class MediaCategoryControlController extends AbstractControlController
{
    /**
     * Lists all Nodes.
     *
     * @Route("/tree", name="btn_media_mediacontrol_tree_category")
     * @Template()
     */
    public function treeAction(Request $request)
    {
        $provider = $this->getEntityProvider();
        $repo     = $provider->getRepository();
        $current  = null;
        if ($request->get('category') !== null) {
            $current = $repo->find($request->get('category'));
        }

        return array(
            'categories'  => $repo->findAll(),
            'currentNode' => $current,
            'modal'       => $request->get('modal')
            );
    }

    /**
     * @Route("/new", name="btn_media_mediacontrol_new_category")
     * @Template("BtnMediaBundle:MediaCategoryControl:form.html.twig")
     */
    public function newAction(Request $request)
    {
        $mediaCategoryProvider = $this->getEntityProvider();
        $entity = $mediaCategoryProvider->create();
        $form   = $this->createForm('btn_media_form_media_category_control', $entity);

        return array('form' => $form->createView());
    }

    /**
     * @Route("/create", name="btn_media_mediacontrol_create_category")
     * @Method({"POST"})
     * @Template("BtnMediaBundle:MediaCategoryControl:form.html.twig")
     */
    public function createAction(Request $request)
    {
        $mediaCategoryProvider = $this->getEntityProvider();
        $entity = $mediaCategoryProvider->create();
        $form   = $this->createForm('btn_media_form_media_category_control', $entity);
        $form->handleRequest($request);
        $category = $form->getData();
        //save MediaCategory entity
        $mediaCategoryProvider->save($entity);

        $this->setFlash('btn_admin.flash.created');

        return $this->redirect($this->generateUrl('btn_media_mediacontrol_edit_category', array('id' => $category->getId())));
    }

    /**
     * @Route("/edit/{id}", name="btn_media_mediacontrol_edit_category")
     * @Template("BtnMediaBundle:MediaCategoryControl:form.html.twig")
     */
    public function editAction(Request $request, $id)
    {
        $mediaCategoryProvider = $this->getEntityProvider();
        $entity = $mediaCategoryProvider->getRepository()->find($id);
        $form = $this->get('btn_media.form.media_category_control');
        $form->setActionRouteName('btn_media_mediacontrol_update_category');
        $form->setActionRouteParams(array('id' => $entity->getId()));
        $form = $this->createForm($form, $entity);
        //save MediaCategory entity
        $mediaCategoryProvider->save($entity);

        return array('form' => $form->createView());
    }

    /**
     * @Route("/update/{id}", name="btn_media_mediacontrol_update_category")
     * @Method({"POST"})
     * @Template("BtnMediaBundle:MediaCategoryControl:form.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $mediaCategoryProvider = $this->getEntityProvider();
        $entity = $mediaCategoryProvider->getRepository()->find($id);
        $form = $this->createForm('btn_media_form_media_category_control', $entity);
        $form->handleRequest($request);
        //save MediaCategory entity
        $mediaCategoryProvider->save($entity);

        $this->setFlash('btn_admin.flash.updated');

        return $this->redirect($this->generateUrl('btn_media_mediacontrol_edit_category', array('id' => $entity->getId())));
    }

    /**
     * @Route("/delete/{id}/{csrf_token}", name="btn_media_mediacontrol_category_delete")
     */
    public function deleteAction(Request $request, $id, $csrf_token)
    {
        $this->validateCsrfTokenOrThrowException('btn_media_mediacontrol_category_delete', $csrf_token);

        $mediaCategoryProvider = $this->getEntityProvider();
        $category              = $mediaCategoryProvider->getRepository()->find($id);

        if ($category) {
            $mediaCategoryProvider->delete($category);
            $this->setFlash('btn_admin.flash.deleted');
        }

        return $this->redirect($this->generateUrl('btn_media_mediacontrol_media_index'));
    }
}
