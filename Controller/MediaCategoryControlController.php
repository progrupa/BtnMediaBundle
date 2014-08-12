<?php

namespace Btn\MediaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Btn\AdminBundle\Controller\AbstractControlController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Media controller.
 *
 * @Route("/media/category")
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
        $provider = $this->get('btn_media.provider.media_category');
        $repo     = $provider->getRepository();
        $current  = null;
        if ($request->get('category') !== null) {
            $current = $repo->find($request->get('category'));
        }

        return array('categories' => $repo->findAll(), 'currentNode' => $current);
    }

    /**
     * @Route("/new", name="btn_media_mediacontrol_new_category")
     * @Template("BtnMediaBundle:MediaCategoryControl:form.html.twig")
     **/
    public function newAction(Request $request)
    {
        $mediaCategoryProvider = $this->get('btn_media.provider.media_category');
        $entity = $mediaCategoryProvider->create();
        $form   = $this->createForm('btn_media_form_mediacategory', $entity);

        return array('form' => $form->createView());
    }

    /**
     * @Route("/create", name="btn_media_mediacontrol_create_category")
     * @Method({"POST"})
     * @Template("BtnMediaBundle:MediaCategoryControl:form.html.twig")
     **/
    public function createAction(Request $request)
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
     * @Route("/edit/{id}", name="btn_media_mediacontrol_edit_category")
     * @Template("BtnMediaBundle:MediaCategoryControl:form.html.twig")
     **/
    public function editAction(Request $request, $id)
    {
        $mediaCategoryProvider = $this->get('btn_media.provider.media_category');
        $entity = $mediaCategoryProvider->getRepository()->find($id);
        $form = $this->get('btn_media.form.mediacategory');
        $form->setActionRouteName('btn_media_mediacontrol_update_category');
        $form->setActionRouteParams(array('id' => $entity->getId()));
        $form   = $this->createForm($form, $entity);
        // $form->handleRequest($request);
        //save MediaCategory entity
        $mediaCategoryProvider->save($entity);

        return array('form' => $form->createView());
    }

    /**
     * @Route("/update/{id}", name="btn_media_mediacontrol_update_category")
     * @Method({"POST"})
     * @Template("BtnMediaBundle:MediaCategoryControl:form.html.twig")
     **/
    public function updateAction(Request $request, $id)
    {
        $mediaCategoryProvider = $this->get('btn_media.provider.media_category');
        $entity = $mediaCategoryProvider->getRepository()->find($id);
        $form = $this->createForm('btn_media_form_mediacategory', $entity);
        $form->handleRequest($request);
        //save MediaCategory entity
        $mediaCategoryProvider->save($entity);

        return $this->redirect($this->generateUrl('btn_media_mediacontrol_edit_category', array('id' => $entity->getId())));
    }

    /**
     * @Route("/delete/{id}", name="btn_media_mediacontrol_category_delete")
     **/
    public function deleteAction(Request $request, $id)
    {
        $mediaCategoryProvider = $this->get('btn_media.provider.media_category');
        $category              = $mediaCategoryProvider->getRepository()->find($id);

        if ($category) {
            $mediaCategoryProvider->delete($category);
        }

        return $this->redirect($this->generateUrl('btn_media_mediacontrol_media_index'));
    }
}
