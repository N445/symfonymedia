<?php

namespace N445\SymfonyMedia\Fixture;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImagesFixture
{
    public static function getFixtureImage()
    {
        $srcDir = __DIR__ . '/images/';
        $copyDir = __DIR__ . '/copy/';
        $images = scandir($srcDir);

        if (($key = array_search('.', $images)) !== false) {
            unset($images[$key]);
        }

        if (($key = array_search('..', $images)) !== false) {
            unset($images[$key]);
        }

        $image     = $images[array_rand($images)];
        $imageCopy = uniqid('copy-', true) . $image;
        copy($srcDir . $image, $copyDir . $imageCopy);
        return (new UploadedFile($copyDir.$imageCopy, $imageCopy, null, null, true));
    }
}