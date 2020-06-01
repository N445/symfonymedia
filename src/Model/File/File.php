<?php

namespace N445\SymfonyMedia\Model\File;

use N445\SymfonyMedia\Model\FileBase;
use Symfony\Component\Validator\Constraints as Assert;

abstract class File extends FileBase
{
    /**
     * @Assert\File(maxSize="15000000")
     */
    protected $file;
}