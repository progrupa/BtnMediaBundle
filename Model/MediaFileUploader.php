<?php

namespace Btn\MediaBundle\Model;

use Btn\MediaBundle\Entity\MediaFile;
use Btn\MediaBundle\Entity\MediaFileCategory;
use Doctrine\ORM\EntityManager;
use Gaufrette\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaFileUploader
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var array
     */
    private $allowedExtensions;

    /**
     * @var int
     */
    private $sizeLimit;

    /**
     * @var bool
     */
    private $replaceOldFiles;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var MediaFileCategory
     */
    private $category;

    /**
     * @var array
     */
    private $errors;

    /**
     * @var array
     */
    private $uploadedFiles;

    /**
     * @var string
     */
    private $cacheDirectory;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, $cacheDirectory)
    {
        $this->em             = $em;
        $this->cacheDirectory = $cacheDirectory;

        $this->reset();
    }

    public function reset()
    {
        $this->allowedExtensions = array();
        $this->sizeLimit         = $this->toBytes(ini_get('upload_max_filesize'));
        $this->filesystem        = null;
        $this->category          = null;
        $this->replaceOldFiles   = false;
        $this->file              = null;
        $this->errors            = array();
        $this->uploadedFiles     = array();
    }

    /**
     * @param $error
     *
     * @return MediaFileUploader
     */
    public function addError($error)
    {
        $this->errors[] = $error;

        return $this;
    }

    /**
     * @return string
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * @param array $allowedExtensions
     *
     * @return MediaFileUploader
     */
    public function setAllowedExtensions(array $allowedExtensions)
    {
        $this->allowedExtensions = array_map('strtolower', $allowedExtensions);

        return $this;
    }

    /**
     * @return array
     */
    public function getAllowedExtensions()
    {
        return $this->allowedExtensions;
    }

    /**
     * @param $sizeLimit
     *
     * @return MediaFileUploader
     */
    public function setSizeLimit($sizeLimit)
    {
        $this->sizeLimit = $sizeLimit;

        $this->checkServerSettings();

        return $this;
    }

    /**
     * @return int
     */
    public function getSizeLimit()
    {
        return $this->sizeLimit;
    }

    /**
     * @param Filesystem $filesystem
     *
     * @return MediaFileUploader
     */
    public function setFilesystem(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;

        return $this;
    }

    /**
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * @param MediaFileCategory $category
     *
     * @return MediaFileUploader
     */
    public function setCategory(MediaFileCategory $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return MediaFileCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param bool $replaceOldFiles
     *
     * @return MediaFileUploader
     */
    public function setReplaceOldFiles($replaceOldFiles)
    {
        $this->replaceOldFiles = (bool)$replaceOldFiles;

        return $this;
    }

    /**
     * @return bool
     */
    public function getReplaceOldFiles()
    {
        return $this->replaceOldFiles;
    }

    /**
     * @return array
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return !empty($this->uploadedFiles);
    }

    /**
     * Internal function that checks if server's may sizes match the
     * object's maximum size for uploads
     */
    private function checkServerSettings()
    {
        $postSize   = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));

        if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit) {
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';
            throw new \Exception('Increase post_max_size and upload_max_filesize to ' . $size);
        }
    }

    /**
     * Convert a given size with units to bytes
     *
     * @param string $str
     */
    private function toBytes($str)
    {
        $val  = trim($str);
        $last = strtolower($str[strlen($str) - 1]);
        switch ($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }

    /**
     * @param UploadedFile $file
     */
    private function saveUpload(UploadedFile $file)
    {
        $media     = new MediaFile();
        $directory = $media->getUploadRootDir();
        $basename  = $file->getClientOriginalName();
        $extension = $file->guessExtension();
        $filename  = preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), pathinfo($basename, PATHINFO_FILENAME));

        if ($this->getReplaceOldFiles() == false) {
            while (file_exists($directory . DIRECTORY_SEPARATOR . $filename . '.' . $extension)) {
                $filename .= rand(0, 9);
            }
        }

        $filename .= '.' . $extension;

        $media->setName($filename);
        $media->setFile($filename);
        $media->setCategory($this->getCategory());
        $media->setType($file->getMimeType());

        if ($this->filesystem) {
            $gaufrette = new \Gaufrette\File($filename, $this->filesystem);
            $gaufrette->setContent(file_get_contents($file->getRealPath()));
        } else {
            $file->move($media->getUploadRootDir(), $filename);
        }

        $this->uploadedFiles[] = $filename;

        $this->em->persist($media);
    }

    /**
     * @param UploadedFile $file
     */
    private function handleZip(UploadedFile $file)
    {
        $zip = new \ZipArchive();
        if ($zip->open($file->getRealPath()) === true) {
            $cacheDirectory = $this->cacheDirectory . '/' . md5(time());
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);

                if (strpos($filename, '__MACOSX') !== false || strpos($filename, '.') === 0) {
                    continue;
                }

                if ($zip->extractTo($cacheDirectory, array($filename)) && is_file($cacheDirectory . '/' . $filename)) {
                    $file = new UploadedFile($cacheDirectory . '/' . $filename, basename($filename));
                    $this->handleFile($file);
                }
            }
            $this->deleteDirectory($cacheDirectory);
            $zip->close();
        } else {
            $this->addError('Could not open ZIP archive.');
        }
    }

    /**
     * @param UploadedFile $file
     *
     * @return MediaFileUploader
     */
    private function handleFile(UploadedFile $file)
    {
        if ($file == null) {
            return $this->addError('No files were uploaded.');
        }

        if ($file->getSize() == 0) {
            return $this->addError('File is empty.');
        }

        if ($file->getSize() > $this->sizeLimit) {
            return $this->addError('File is too large.');
        }

        $extension = $file->guessExtension();

        if (empty($extension)) {
            return $this->addError('File has no extension.');
        }

        if ($this->allowedExtensions && !in_array($extension, $this->allowedExtensions)) {
            return $this->addError('File has an invalid extension, it should be one of ' . implode(', ', $this->allowedExtensions) . '.');
        }

        $this->saveUpload($file);
    }

    /**
     * @param string $path
     */
    private function deleteDirectory($path)
    {
        if (is_file($path)) {
            @unlink($path);
        } else {
            array_map(array(__CLASS__, __FUNCTION__), glob($path . '/*'));
        }

        @rmdir($path);
    }

    /**
     * @param UploadedFile $file
     *
     * @return MediaFileUploader
     */
    public function handleUpload(UploadedFile $file)
    {
        if ($file->guessExtension() == 'zip') {
            $this->handleZip($file);
        } else {
            $this->saveUpload($file);
        }

        $this->em->flush();

        return $this;
    }
}