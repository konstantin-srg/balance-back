<?php
namespace Pipe\Image;

use Pipe\Exception\Exception;
use Pipe\File\File;

class Image extends File
{
    /**
     * @var array
     */
    protected $options = array(
        'width'     => 3000,
        'height'    => 3000,
        'watermark' => false,
        'crop'      => false,
    );

    public function save()
    {
        $fileName = $this->getFileName();
        $filePath = $this->getFile();

        list($width, $height, $type) = getimagesize($filePath);

        switch ($type) {
            case IMAGETYPE_GIF:
                $fileType = '.gif';
                break;
            case IMAGETYPE_JPEG:
                $fileType = '.jpg';
                break;
            case IMAGETYPE_PNG:
                $fileType = '.png';
                break;
            default:
                return false;
        }

        if(empty($fileName)) {
            $fileName = rtrim(basename($filePath), strrchr($filePath, "."));
        }

        $fileName .= $fileType;

        $fileName = $this->getUniqueFilename($fileName);

        $newFilePath = $this->getBasePath() . '/' . $fileName;

        if (!copy($filePath, $newFilePath)) {
            return false;
        }

        return $fileName;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    public function createThumbnail()
    {
        if(!is_dir($this->getBasePath())) {
            mkdir($this->getBasePath(), 0777, true);
        }

        $filePath = $this->getFile();

        $fileName = $this->getFileName() ? $this->getFileName() : basename($filePath);

        $newFile = $this->getBasePath() . '/' . $fileName;

        list($sourceWidth, $sourceHeight, $type) = getimagesize($filePath);

        switch ($type) {
            case IMAGETYPE_GIF:
                $imageSource = imagecreatefromgif($filePath);
                break;
            case IMAGETYPE_JPEG:
                $imageSource = imagecreatefromjpeg($filePath);
                break;
            case IMAGETYPE_PNG:
                $imageSource = imagecreatefrompng($filePath);
                break;
            default:
                return false;
        }

        if (!$imageSource) {
            return false;
        }

        if(!$this->options['crop']) {
            $newImage = $this->resizeWithoutCrop($imageSource, $sourceWidth, $sourceHeight, $this->options['width'], $this->options['height']);
        } else {
            $newImage = $this->resizeWithCrop($imageSource, $sourceWidth, $sourceHeight, $this->options['width'], $this->options['height']);
        }

        if($type == IMAGETYPE_PNG) {
            if(empty($resolution['watermark'])) {
                imageAlphaBlending($newImage, false);
                imageSaveAlpha($newImage, true);
            }

            $this->setTransparency($newImage, $imageSource);
        }

        if($this->options['watermark']) {
            //$newImage = $this->setWatermark($newImage);
        }

        imagepng($newImage, $newFile);

        imagedestroy($imageSource);
        imagedestroy($newImage);

        return $newFile;
    }

    protected function setTransparency($newImage, $imageSource)
    {
        $transparencyIndex = imagecolortransparent($imageSource);
        $transparencyColor = array('red' => 255, 'green' => 255, 'blue' => 255);

        if ($transparencyIndex >= 0) {
            $transparencyColor = imagecolorsforindex($imageSource, $transparencyIndex);
        }

        $transparencyIndex = imagecolorallocate($newImage, $transparencyColor['red'], $transparencyColor['green'], $transparencyColor['blue']);
        imagefill($newImage, 0, 0, $transparencyIndex);
        imagecolortransparent($newImage, $transparencyIndex);
    }

    protected function resizeWithoutCrop($source, $sourceWidth, $sourceHeight, $maxWidth, $maxHeight)
    {
        $ratio = $sourceWidth / $sourceHeight;

        $maxWidth = ($maxWidth == 0 ? $sourceWidth : min($sourceWidth, $maxWidth));
        $maxHeight = ($maxHeight == 0 ? $sourceHeight : min($sourceHeight, $maxHeight));

        $newWidth = $maxWidth;
        $newHeight = $newWidth / $ratio;

        if ($newHeight > $maxHeight) {
            $newHeight = $maxHeight;
            $newWidth = $newHeight * $ratio;
        }

        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);

        return $newImage;
    }

    protected function resizeWithCrop($source, $sourceWidth, $sourceHeight, $maxWidth, $maxHeight)
    {
        $source_aspect_ratio = $sourceWidth / $sourceHeight;
        $desired_aspect_ratio = $maxWidth / $maxHeight;

        if ($source_aspect_ratio > $desired_aspect_ratio) {
            $temp_height = $maxHeight;
            $temp_width = (int) ($maxHeight * $source_aspect_ratio);
        } else {
            $temp_width = $maxWidth;
            $temp_height = (int) ($maxWidth / $source_aspect_ratio);
        }

        $temp_gdim = imagecreatetruecolor($temp_width, $temp_height);

        imagecopyresampled($temp_gdim, $source, 0, 0, 0, 0, $temp_width, $temp_height, $sourceWidth, $sourceHeight);

        $x0 = ($temp_width - $maxWidth) / 2;
        $y0 = ($temp_height - $maxHeight) / 2;
        //$y0 = 0;
        $desired_gdim = imagecreatetruecolor($maxWidth, $maxHeight);
        imagecopy($desired_gdim, $temp_gdim, 0, 0, $x0, $y0, $maxWidth, $maxHeight);

        return $desired_gdim;
    }
}
