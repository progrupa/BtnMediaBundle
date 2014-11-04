<?php

namespace Btn\MediaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class NodeContentType extends AbstractType
{
    private $data;

    public function __construct($data = array())
    {
        $this->data = $data;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('category', 'choice', array(
                'empty_value' => 'Select media category',
                'choices'     => $this->data,
            ))
        ;
    }

    public function getName()
    {
        return 'btn_mediabundle_nodecontent';
    }
}
