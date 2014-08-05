<?php

namespace Btn\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MediaFileCategory
 *
* @ORM\Table(name="media_file_category")
 * @ORM\Entity
 */
class MediaFileCategory
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="Btn\MediaBundle\Entity\MediaFile", mappedBy="category")
     */
    private $files;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return MediaFileCategory
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->files = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add files
     *
     * @param \Btn\MediaBundle\Entity\MediaFile $files
     * @return MediaFileCategory
     */
    public function addFile(\Btn\MediaBundle\Entity\MediaFile $files)
    {
        $this->files[] = $files;

        return $this;
    }

    /**
     * Remove files
     *
     * @param \Btn\MediaBundle\Entity\MediaFile $files
     */
    public function removeFile(\Btn\MediaBundle\Entity\MediaFile $files)
    {
        $this->files->removeElement($files);
    }

    /**
     * Get files
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     *
     */
    public function __toString()
    {
        return $this->getName();
    }
}
