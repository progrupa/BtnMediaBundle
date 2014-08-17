<?php

namespace Btn\MediaBundle\Adapter;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\AbstractType;
use Btn\BaseBundle\Provider\EntityProviderInterface;
use Btn\MediaBundle\Adapter\AdapterInterface;

/**
* DefaultAdapter
*/
class DefaultAdapter implements AdapterInterface
{
    /** @var \Btn\BaseBundle\Provider\EntityProviderInterface $mediaProvider */
    protected $mediaProvider;
    /** @var \Btn\BaseBundle\Provider\EntityProviderInterface $mediaCategoryProvider */
    protected $mediaCategoryProvider;
    /** @var \Symfony\Component\Form\FormFactoryInterface $formFactory */
    protected $formFactory;
    /** @var \Symfony\Component\Form\AbstractType $form */
    protected $form = null;
    /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
    protected $file = null;

    /**
     * @param EntityProviderInterface   $mediaProvider
     * @param EntityProviderInterface   $mediaCategoryProvider
     * @param FormFactory               $formFactory
     * @param AbstractType              $form instance of form AbstractType
     */
    public function __construct(
        EntityProviderInterface $mediaProvider,
        EntityProviderInterface $mediaCategoryProvider,
        FormFactoryInterface $formFactory,
        AbstractType $form
        )
    {
        $this->mediaProvider         = $mediaProvider;
        $this->mediaCategoryProvider = $mediaCategoryProvider;
        $this->formFactory           = $formFactory;
        $this->form                  = $form;
    }

    public function createForm(Request $request = null, $mediaFile = null)
    {
        $entity = $mediaFile ? $mediaFile : $this->mediaProvider->create();
        //bind entity with category, if category is set as GET param
        if ($request && ($category = $request->get('category'))) {
            $category = $this->mediaCategoryProvider->getRepository()->find($category);
            $entity->setCategory($category);
        }
        //change form action route params
        if ($entity->getId()) {
            $this->form->setActionRouteParams(array('id' => $entity->getId()));
        }
        //create form based on form type service and set data
        $form = $this->formFactory->create($this->form, $entity);
        //bind form with the request if avaible
        if ($request) {
            $form->handleRequest($request);
            //set uploaded file from request FileBag
            $file = $request->files->get($form->getName());
            $this->setUploadedFile(is_array($file) ? array_pop($file) : $file);
        }

        $this->setForm($form);

        return $form;
    }

    /**
     * Return UploadFile object from binded form
     *
     * @return UploadFile $file
     */
    public function getUploadedFile()
    {
        return $this->file;
    }

    /**
     * Set UploadedFile object
     *
     * @param UploadedFile $file
     * @return AdapterInterface
     */
    public function setUploadedFile(UploadedFile $file = null)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return Media or mixed
     */
    public function getFormData()
    {
        return $this->form->getData();
    }

    /**
     * @return ArrayCollection of Media instances
     */
    public function getIndexViewData($category = null)
    {
        $category      = $category ? $this->mediaCategoryProvider->getRepository()->find($category) : null;
        $mediaFileRepo = $this->mediaProvider->getRepository();

        return $category ? $mediaFileRepo->findByCategory($category) : $mediaFileRepo->findAll();
    }

    /**
     * Set variable $form
     *
     * @param FormType $form
     */
    private function setForm($form)
    {
        return $this->form = $form;
    }
}
