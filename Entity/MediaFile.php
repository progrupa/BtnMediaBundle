<?php

namespace Btn\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Btn\MediaBundle\Util\File;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * MediaFile
 *
 * @ORM\Table(name="media_file")
 * @ORM\Entity(repositoryClass="Btn\MediaBundle\Repository\MediaFileRepository")
 * @ORM\HasLifecycleCallbacks
 */
class MediaFile extends File
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
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Btn\MediaBundle\Entity\MediaFileCategory", inversedBy="files")
     * @ORM\JoinColumn(name="media_file_category_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $category;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="file", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $file;

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
     * @return MediaFile
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    protected function getUploadDir()
    {
        return 'uploads/media';
    }

    public function getMediaPath()
    {
        return $this->getUploadRootDir() . '/' . $this->file;
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
     * Set file
     *
     * @param string $file
     * @return RestaurantFile
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get file
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;

        //DEPRACATED
        $path = $this->getPath();
        //if file is PDF or is not accesible, render 'no preview'
        $extension = explode('.', $this->file);
        if ((is_array($extension) && isset($extension[1]) && strtolower($extension[1]) === 'pdf') || !file_exists($path)) {
            //TODO should be constants or from params ?
            $path = 'images/no_preview.jpeg';
        }

        return $path;
    }

    /**
     *
     */
    public function getFileExt()
    {
        $file = $this->getFile();

        return $file ? strtolower(substr($file, strrpos($file, ".") + 1)) : $file;
    }

    /**
     *
     */
    public function getDefaultFilePath()
    {
        return 'no_preview.jpeg';
    }

    /**
     * Set description
     *
     * @param string $description
     * @return MediaFile
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return MediaFile
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Set category
     *
     * @param string $category
     * @return MediaFile
     */
    public function setCategory(\Btn\MediaBundle\Entity\MediaFileCategory $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Get category name
     */
    public function getCategoryName()
    {
        return $this->category->getName();
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeFile()
    {
        if ($file = $this->getMediaPath()) {
            if (file_exists($file)) {
                @unlink($file);
            }
        }
    }

    public function getPath()
    {
        return $path = $this->getUploadDir() . DIRECTORY_SEPARATOR . $this->file;
    }
}
