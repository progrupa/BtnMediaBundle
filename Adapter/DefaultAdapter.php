<?php

namespace Btn\MediaBundle\Adapter;

use Btn\NodesBundle\Service\NodeContentProviderInterface;
use Btn\MediaBundle\Form\NodeContentType;
use Btn\MediaBundle\Entity\MediaFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\AbstractType as FormType;
use Exception;

/**
* DefaultAdapter
*/
class DefaultAdapter
{
    /**
     * @var FormFactory $formFactory
     */
    private $formFactory;

    /**
     * @var string $formName
     */
    private $formName;

    /**
     * @var Form $form
     */
    private $form = null;

    /**
     * @param FormFactory $formFactory
     * @param string $formName name of form service or instance of form AbstractType
     */
    public function __construct($em, FormFactoryInterface $formFactory, $formName)
    {
        $this->formFactory = $formFactory;
        $this->formName    = $formName;
        $this->em          = $em;
        if ($formName instanceof FormType) {
            $this->form = $formName;
        }
    }

    public function createForm(Request $request = null, $mediaFile = null)
    {
        $entity = $mediaFile ? $mediaFile : new MediaFile();
        $form = $this->formFactory->create($this->formName, $entity); //default params ($type, $data, $options)
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

            throw new Exception("UploadAdapter: Method getUploadedFile didn't returned UploadedFile object");
        }

        return $file;
    }

    /**
     * @return MediaFile or mixed
     */
    public function getFormData()
    {
        return $this->form->getData();
    }

    /**
     * @return ArrayCollection of MediaFile instances
     */
    public function getIndexViewData($category = null)
    {
        $category      = $category ? $this->getRepository('BtnMediaBundle:MediaFileCategory')->find($category) : null;
        $mediaFileRepo = $this->em->getRepository('BtnMediaBundle:MediaFile');

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
