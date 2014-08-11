<?php

namespace Btn\MediaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FileType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'empty_value' => 'btn_media.type.file.empty_value',
            'label'       => 'btn_media.type.file.label'
        ));
    }

    public function getParent()
    {
        return 'file';
    }

    public function getName()
    {
        return 'btn_media_type_file';
    }
}
