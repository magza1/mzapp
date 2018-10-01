<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Brand;

use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;

/**
 * Class FileInfo
 * @package Aheadworks\ShopByBrand\Model\Brand
 */
class FileInfo
{
    /**
     * @var ImageUploader
     */
    private $imageUploader;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var Mime
     */
    private $mime;

    /**
     * @param ImageUploader $imageUploader
     * @param Filesystem $filesystem
     * @param Mime $mime
     */
    public function __construct(
        ImageUploader $imageUploader,
        Filesystem $filesystem,
        Mime $mime
    ) {
        $this->imageUploader = $imageUploader;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->mime = $mime;
    }

    /**
     * Retrieve MIME type of file
     *
     * @param string $fileName
     * @return string
     */
    public function getMimeType($fileName)
    {
        $absoluteFilePath = $this->mediaDirectory->getAbsolutePath(
            $this->getFilePath($fileName)
        );
        return $this->mime->getMimeType($absoluteFilePath);
    }

    /**
     * Get file statistics data
     *
     * @param string $fileName
     * @return array
     */
    public function getStat($fileName)
    {
        return $this->mediaDirectory->stat($this->getFilePath($fileName));
    }

    /**
     * Check if file exists
     *
     * @param string $fileName
     * @return bool
     */
    public function isExist($fileName)
    {
        return $this->mediaDirectory->isExist($this->getFilePath($fileName));
    }

    /**
     * Get file path
     *
     * @param string $fileName
     * @return string
     */
    private function getFilePath($fileName)
    {
        return $this->imageUploader->getFilePath(
            $this->imageUploader->getBasePath(),
            $fileName
        );
    }
}
