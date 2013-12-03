<?php
namespace Btn\MediaBundle\Util;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class File
{
    private $fieldFile     = 'file';
    private $fieldPath     = 'path';
    private $mimeTypes = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

    public function handleUpload($options = array()) {
        if (count($options)) {
            foreach ($options as $key => $value) {
                $this->$key = $value;
            }
        }

        $old = $this->getUploadRootDir() . '/' . $this->getPath();

        if ($this->getFile()) {
            if (file_exists($this->getUploadRootPath())) {
                $p = $this->getUploadRootPath();
                unset($p);
            }

            // do whatever you want to generate a unique name
            $this->setPath(sha1(uniqid(mt_rand(), true)).'.'.$this->getFile()->guessExtension());
            $this->getFile()->move($this->getUploadRootDir(), $this->getPath());

            @unlink($old);
            if (file_exists($this->getFile())) {
                $p = $this->getFile();
                unset($p);
            }

            $this->setFile(null);
        }

        return $this;
    }

    private function getFile()
    {
         $f = 'get' . ucfirst($this->fieldFile);

         return $this->$f();
    }

    private function setFile($file)
    {
         $f = 'set' . ucfirst($this->fieldFile);

         return $this->$f($file);
    }

    private function getPath()
    {
         $f = 'get' . ucfirst($this->fieldPath);

         return $this->$f();
    }

    private function setPath($path)
    {
         $f = 'set' . ucfirst($this->fieldPath);

         return $this->$f($path);
    }

    private function getUploadRootPath()
    {
         return $this->getUploadRootDir() . $this->getPath();
    }

    public function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

}