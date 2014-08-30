<?php

namespace Btn\MediaBundle\Uploader;

use Gaufrette\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Btn\MediaBundle\Adapter\AdapterInterface;

class MediaUploader
{
    /** @var array $allowedExtensions */
    private $allowedExtensions;
    /** @var int $sizeLimit */
    private $sizeLimit;
    /** @var bool $replaceOldFiles */
    private $replaceOldFiles;
    /** @var \Gaufrette\Filesystem $filesystem */
    private $filesystem;
    /** @var array $errors */
    private $errors;
    /** @var array $uploadedFiles */
    private $uploadedFiles;
    /** @var array $uploadedMedias */
    private $uploadedMedias;
    /** @var string $cacheDirectory */
    private $cacheDirectory;
    /** @var Btn\MediaBundle\Adapter\AdapterInterface\ $adapter */
    private $adapter;

    /**
     *
     */
    public function __construct($cacheDirectory)
    {
        $this->cacheDirectory = $cacheDirectory;

        $this->reset();
    }

    /**
     *
     */
    public function reset()
    {
        $this->allowedExtensions = array();
        $this->sizeLimit         = $this->toBytes(ini_get('upload_max_filesize'));
        $this->filesystem        = null;
        $this->replaceOldFiles   = false;
        $this->file              = null;
        $this->errors            = array();
        $this->uploadedFiles     = array();
        $this->uploadedMedias    = array();
    }

    /**
     * @param $error
     *
     * @return MediaUploader
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
     * @return MediaUploader
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
     * @return MediaUploader
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
     * @return MediaUploader
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
     * @param bool $replaceOldFiles
     *
     * @return MediaUploader
     */
    public function setReplaceOldFiles($replaceOldFiles)
    {
        $this->replaceOldFiles = (bool) $replaceOldFiles;

        return $this;
    }

    /**
     *
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
            $size = max(1, $this->sizeLimit / 1024 / 1024).'M';
            throw new \Exception('Increase post_max_size and upload_max_filesize to '.$size);
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
                // no break
            case 'm':
                $val *= 1024;
                // no break
            case 'k':
                $val *= 1024;
                // no break
        }

        return $val;
    }

    /**
     * @param UploadedFile $file
     *
     */
    private function saveUpload(UploadedFile $file = null)
    {
        $media = $this->adapter->getFormData();
        if ($file) {
            $extension = $file->guessExtension();
            $filename  = $basename = preg_replace(
                array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'),
                array('_', '.', ''),
                pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)
            );

            if (false == $this->getReplaceOldFiles()) {
                $counter = 0;
                if ($this->filesystem) {
                    while ($this->filesystem->has($filename.'.'.$extension)) {
                        $filename = $basename.$counter++;
                    }
                } else {
                    $directory = $media->getUploadRootDir();
                    while (file_exists($directory.DIRECTORY_SEPARATOR.$filename.'.'.$extension)) {
                        $filename = $basename.$counter++;
                    }
                }
            }

            $filename .= '.'.$extension;

            $media->setName($media->getName() ? $media->getName() : $filename);
            $media->setFile($filename);
            $media->setType($file->getMimeType());

            if ($this->filesystem) {
                $gaufrette = $this->filesystem->get($filename, true);
                $gaufrette->setContent(file_get_contents($file->getRealPath()));
            } else {
                $file->move($media->getUploadRootDir(), $filename);
            }

            $this->uploadedFiles[] = $filename;
        }
        $this->uploadedMedias[] = $media;
    }

    /**
     * @param UploadedFile $file
     */
    private function handleZip(UploadedFile $file)
    {
        $zip = new \ZipArchive();
        if ($zip->open($file->getRealPath()) === true) {
            $cacheDirectory = $this->cacheDirectory.'/'.md5(time());
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);

                if (strpos($filename, '__MACOSX') !== false || strpos($filename, '.') === 0) {
                    continue;
                }

                if ($zip->extractTo($cacheDirectory, array($filename)) && is_file($cacheDirectory.'/'.$filename)) {
                    $file = new UploadedFile($cacheDirectory.'/'.$filename, basename($filename));
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
     * @return MediaUploader
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
            return $this->addError(
                'File has an invalid extension, it should be one of '.implode(', ', $this->allowedExtensions).'.'
            );
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
            array_map(array(__CLASS__, __FUNCTION__), glob($path.'/*'));
        }

        @rmdir($path);
    }

    /**
     * @param UploadedFile $file
     *
     * @return MediaUploader
     */
    public function handleUpload(UploadedFile $file = null)
    {
        if (null === $file) {
            if (!$this->adapter) {
                throw new \Exception(sprintf('Adapter is missing in "%s". set it via setAdapter()', __CLASS__));
            }
            $file = $this->adapter->getUploadedFile();
        }

        if ($file) {
            if ('zip' === $file->guessExtension()) {
                $this->handleZip($file);
            } else {
                $this->saveUpload($file);
            }
        }

        return $this;
    }

    /**
     * Set adapter and handleUpload
     */
    public function setAdapter(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * @return array
     */
    public function getUploadedMedias()
    {
        return $this->uploadedMedias;
    }
}
