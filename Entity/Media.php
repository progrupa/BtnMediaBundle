<?php

namespace Btn\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="btn_media")
 * @ORM\Entity(repositoryClass="Btn\MediaBundle\Repository\MediaRepository")
 */
class Media extends AbstractMedia
{
    /**
     * @ORM\ManyToOne(targetEntity="Btn\MediaBundle\Entity\MediaCategory", inversedBy="files", cascade={"persist"})
     * @ORM\JoinColumn(name="media_category_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $category;
}
