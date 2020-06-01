<?php

namespace N445\SymfonyMedia\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class FileBase
{
    const PUBLIC_DIR       = 'public/';
    const PROJECT_DIR      = '/../../../../../'; // todo revoir ça
    const FIXTURE_IMG_NAME = 'fixture.jpg';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $path;

    protected $temp;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return FileBase
     */
    protected function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     * @return FileBase
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Sets file.
     *
     * @param File $file
     * @return FileBase
     */
    public function setFile(File $file = null)
    {
        $this->file = $file;
        // check if we have an old image path
        if (isset($this->path)) {
            // store the old name to delete after the update
            $this->temp = $this->path;
            $this->path = null;
        } else {
            $this->path = 'initial';
        }
        return $this;
    }

    /**
     * Get file.
     *
     * @return File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->getFile()) {
            $filename   = $this->getFilename();
            $this->path = $filename . '.' . $this->getFile()->guessExtension();
        }
    }

    public function getFilename()
    {
        return substr(sha1(uniqid(mt_rand(), true)), 0, 8);
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->getFile()) {
            return;
        }
        $this->getFile()->move($this->getUploadRootDir(), $this->path);

        if (isset($this->temp) && $this->temp != self::FIXTURE_IMG_NAME) {
            @unlink($this->getUploadRootDir() . '/' . $this->temp);
            $this->temp = null;
        }
        $this->file = null;
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        $file = $this->getAbsolutePath();

        if ($file && $this->path != self::FIXTURE_IMG_NAME) {
            @unlink($file);
        }
    }

    public function getAbsolutePath()
    {
        return null === $this->path
            ? null
            : $this->getUploadRootDir() . '/' . $this->path;
    }

    public function getWebPath()
    {
        return null === $this->path
            ? null
            : $this->getUploadDir() . '/' . $this->path;
    }

    protected function getUploadRootDir()
    {
        return __DIR__ . self::PROJECT_DIR . self::PUBLIC_DIR . $this->getUploadDir();
    }

    /**
     * @return mixed
     */
    public function getTemp()
    {
        return $this->temp;
    }

}