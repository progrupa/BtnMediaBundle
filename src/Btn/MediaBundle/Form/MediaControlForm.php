<?php

namespace Btn\MediaBundle\Form;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormInterface;
use Btn\MediaBundle\Helper\MimeTypeHelper;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaControlForm extends AbstractForm
{
    /** @var string $actionRouteName */
    protected $actionRouteName = 'btn_media_mediacontrol_media_upload';
    /** @var array $allowedExtensions */
    protected $allowedExtensions;
    /** @var array $allowedMimeTypes */
    protected $allowedMimeTypes;
    /** @var string $maxSize */
    protected $maxSize;

    /**
     *
     */
    public function setAllowedExtensions(array $allowedExtensions)
    {
        $this->allowedExtensions = $allowedExtensions;
        $this->allowedMimeTypes  = MimeTypeHelper::getMimeTypesFromExtensions($allowedExtensions);
    }

    /**
     *
     */
    public function setMaxSize($maxSize)
    {
        $this->maxSize = $maxSize;
    }

    /**
     *
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('file', 'btn_media_type_file', array(
                'mapped'      => false,
                'constraints' => array(
                    new Assert\File(array(
                        'groups'    => array('fileValidation'),
                        'maxSize'   => $this->maxSize ?: ini_get('upload_max_filesize'),
                        'mimeTypes' => $this->allowedMimeTypes,
                    )),
                ),
            ))
            ->add('category', 'btn_media_category', array(
            ))
            ->add('name', null, array(
                'label' => 'btn_media.name.label',
            ))
            ->add('description', 'textarea', array(
                'label' => 'btn_media.description.label',
            ))
        ;
    }

    /**
     *
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'action' => $this->router->generate($this->getActionRouteName(), $this->getActionRouteParams()),
            'validation_groups' => function (FormInterface $form) {
                $file = $form->get('file');
                $fileData = $file->getData();

                if (null !== $fileData && $fileData instanceof UploadedFile && $fileData->isFile()) {
                    return array(Constraint::DEFAULT_GROUP, 'fileValidation');
                }

                if (null === $fileData) {
                    return array(Constraint::DEFAULT_GROUP, 'fileMissing');
                }

                return array(Constraint::DEFAULT_GROUP);
            },
        ));
    }

    /**
     * Return form name
     * @return string
     */
    public function getName()
    {
        return 'btn_media_form_media_control';
    }
}
