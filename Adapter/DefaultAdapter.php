<?php

namespace Btn\MediaBundle\Adapter;

use Btn\NodesBundle\Service\NodeContentProviderInterface;
use Btn\MediaBundle\Form\NodeContentType;
use Btn\MediaBundle\Entity\Media;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\AbstractType;
use Btn\AdminBundle\Provider\EntityProviderInterface;

/**
* DefaultAdapter
*/
class DefaultAdapter
{
    /** @var \Btn\AdminBundle\Provider\EntityProviderInterface $mediaProvider */
    protected $mediaProvider;
    /** @var \Btn\AdminBundle\Provider\EntityProviderInterface $mediaCategoryProvider */
    protected $mediaCategoryProvider;
    /** @var \Symfony\Component\Form\FormFactoryInterface $formFactory */
    protected $formFactory;
    /** @var string $formName */
    protected $formName;
    /** @var \Symfony\Component\Form\AbstractType $form */
    protected $form = null;

    /**
     * @param FormFactory $formFactory
     * @param string $formName name of form service or instance of form AbstractType
     */
    public function __construct(EntityProviderInterface $mediaProvider, EntityProviderInterface $mediaCategoryProvider, FormFactoryInterface $formFactory, $formName)
    {
        $this->mediaProvider         = $mediaProvider;
        $this->mediaCategoryProvider = $mediaCategoryProvider;
        $this->formFactory           = $formFactory;
        $this->formName              = $formName;
        if ($formName instanceof AbstractType) {
            $this->form = $formName;
        }
    }

    public function createForm(Request $request = null, $mediaFile = null)
    {
        $entity = $mediaFile ? $mediaFile : $this->mediaProvider->create();
        $form   = $this->formFactory->create($this->formName, $entity);

        if ($request) {
            $form->handleRequest($request);
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
        $file = $this->form->getData()->getFile();
        if (!$mediaFile instanceof UploadedFile) {

            throw new \Exception("UploadAdapter: Method getUploadedFile didn't returned UploadedFile object");
        }

        return $file;
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
