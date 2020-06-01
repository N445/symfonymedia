<?php

namespace N445\SymfonyMedia\Model\File;

use N445\SymfonyMedia\Model\FileBase;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\HasLifecycleCallbacks()
 */
abstract class ImageCompressed extends FileBase
{

    const MAX_WIDTH = 1900;

    /**
     * @Assert\Image(
     *     minWidth = 200,
     *     minHeight = 200,
     *     maxSize="12000000"
     * )
     */
    protected $file;


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
        $this->resize();

        if (isset($this->temp) && $this->temp != self::FIXTURE_IMG_NAME) {
            @unlink($this->getUploadRootDir() . '/' . $this->temp);
            $this->temp = null;
        }
        $this->file = null;
    }


    function resize()
    {
        $info = getimagesize($this->getAbsolutePath());
        $mime = $info['mime'];

        switch ($mime) {
            case 'image/jpeg':
                $image_create_func = 'imagecreatefromjpeg';
                $image_save_func   = 'imagejpeg';
                break;
            case 'image/png':
                $image_create_func = 'imagecreatefrompng';
                $image_save_func   = 'imagepng';
                break;
            case 'image/gif':
                $image_create_func = 'imagecreatefromgif';
                $image_save_func   = 'imagegif';
                break;
            default:
                throw new Exception("Type d'image inconnu.");
        }

        $img = $image_create_func($this->getAbsolutePath());
        list($width, $height) = getimagesize($this->getAbsolutePath());

        if ($width < self::MAX_WIDTH) {
            return;
        }

        $newHeight = ($height / $width) * self::MAX_WIDTH;
        $tmp       = imagecreatetruecolor(self::MAX_WIDTH, $newHeight);
        imagecopyresampled($tmp, $img, 0, 0, 0, 0, self::MAX_WIDTH, $newHeight, $width, $height);

        if (file_exists($this->getAbsolutePath())) {
            unlink($this->getAbsolutePath());
        }
        $image_save_func($tmp, $this->getAbsolutePath());
    }
}