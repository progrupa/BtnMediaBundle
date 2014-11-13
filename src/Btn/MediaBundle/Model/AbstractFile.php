<?php

namespace Btn\MediaBundle\Model;

abstract class AbstractFile
{
    private $fieldFile = 'file';
    private $fieldPath = 'path';

    /**
     *
     */
    public function getFile()
    {
         $method = 'get'.ucfirst($this->fieldFile);

         return $this->$method();
    }

    /**
     *
     */
    public function setFile($file)
    {
         $method = 'set'.ucfirst($this->fieldFile);

         return $this->$method($file);
    }

    /**
     *
     */
    public function getPath()
    {
         $method = 'get'.ucfirst($this->fieldPath);

         return $this->$method();
    }

    /**
     *
     */
    public function setPath($path)
    {
         $method = 'set'.ucfirst($this->fieldPath);

         return $this->$method($path);
    }

    /**
     *
     */
    public function getUploadRootPath()
    {
         return $this->getUploadRootDir().$this->getPath();
    }

    /**
     *
     */
    public function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__.'/../../../../../../web/'.$this->getUploadDir();
    }
}
