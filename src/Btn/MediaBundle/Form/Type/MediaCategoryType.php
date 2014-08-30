<?php

namespace Btn\MediaBundle\Form\Type;

use Btn\AdminBundle\Form\Type\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class MediaCategoryType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'empty_value'   => 'btn_media.form.type.media_category.empty_value',
            'label'         => 'btn_media.form.type.media_category.label',
            'class'         => $this->entityProvider->getClass(),
            'query_builder' => function (EntityRepository $em) {
                return $em
                    ->createQueryBuilder('mc')
                    ->orderBy('mc.name', 'ASC')
                ;
            },
            'property' => 'name',
            'expanded' => false,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'entity';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'btn_media_category';
    }
}
