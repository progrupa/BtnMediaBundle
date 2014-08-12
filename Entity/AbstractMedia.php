<?php

namespace Btn\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Btn\MediaBundle\Model\File;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass()
 * @ORM\HasLifecycleCallbacks()
 */
abstract class AbstractMedia extends File implements MediaInterface
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
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @var integer
     */
    protected $category;

    /**
     * @var string
     *
     * @ORM\Column(name="file", type="string", length=255)
     */
    private $file;

    /**
     *
     */
    private $previewExtensions = array('jpeg', 'jpg', 'png', 'gif');

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
     * @param  string    $name
     * @return Media
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     *
     */
    protected function getUploadDir()
    {
        return 'uploads/media';
    }

    /**
     *
     */
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
     * @param  string         $file
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
     *
     */
    public function getPreviewFilePath()
    {
        $extension = $this->getFileExt();
        if (($extension && !in_array(strtolower($extension), $this->previewExtensions))) {
            return $this->getDefaultFilePath();
        }

        return $this->getFile();
    }

    /**
     * Set description
     *
     * @param  string    $description
     * @return Media
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
     * @param  string    $type
     * @return Media
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

    /**
     * Set category
     *
     * @param  string    $category
     * @return Media
     */
    public function setCategory(MediaCategoryInterface $category = null)
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

    /**
     *
     */
    public function getPath()
    {
        return $path = $this->getUploadDir() . DIRECTORY_SEPARATOR . $this->file;
    }

    /**
     *
     */
    public function setPreviewExtensions(array $previewExtensions)
    {
        $this->previewExtensions = $previewExtensions;
    }

    /**
     *
     */
    public function getPreviewExtensions()
    {
        return $this->previewExtensions;
    }

    /**
     *
     */
    public function __toString()
    {
        return $this->getName();
    }
}
