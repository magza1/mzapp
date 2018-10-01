<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Brand\Image;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Image;
use Magento\Framework\Image\Factory as ImageFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Management
 * @package Aheadworks\ShopByBrand\Model\Brand\Image
 */
class Management
{
    /**
     * Base path to brand logo images
     */
    const IMAGE_PATH = 'aw_sbb/brand';

    /**
     * @var ImageFactory
     */
    private $imageProcessorFactory;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var AssetRepository
     */
    private $assetRepo;

    /**
     * @var array
     */
    private $imageTypes = [];

    /**
     * @param ImageFactory $imageProcessorFactory
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     * @param AssetRepository $assetRepo
     * @param array $imageTypes
     */
    public function __construct(
        ImageFactory $imageProcessorFactory,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        AssetRepository $assetRepo,
        $imageTypes = []
    ) {
        $this->imageProcessorFactory = $imageProcessorFactory;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->storeManager = $storeManager;
        $this->imageTypes = $imageTypes;
        $this->assetRepo = $assetRepo;
    }

    /**
     * Check if logo has an image of given type
     *
     * @param string $imageType
     * @param string $fileName
     * @return bool
     */
    public function hasImage($imageType, $fileName)
    {
        return $this->mediaDirectory->isExist($this->getImagePath($imageType, $fileName));
    }

    /**
     * Creates an logo image of given type
     *
     * @param string $imageType
     * @param string $fileName
     * @throws \Exception
     * @return void
     */
    public function createImage($imageType, $fileName)
    {
        $this->copyAndResize(
            $fileName,
            $this->getImagePath($imageType, $fileName),
            $this->getImageTypeData($imageType, 'imageSize')
        );
    }

    /**
     * Get logo image url of given type
     *
     * @param string $imageType
     * @param string $fileName
     * @return string
     */
    public function getImageUrl($imageType, $fileName)
    {
        /** @var StoreInterface|Store $store */
        $store = $this->storeManager->getStore();
        return $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
            . $this->getImagePath($imageType, $fileName);
    }

    /**
     * Get image placeholder url
     *
     * @param string $imageType
     * @return string
     * @throws \Exception
     */
    public function getImagePlaceholderUrl($imageType)
    {
        return $this->assetRepo->getUrl($this->getImageTypeData($imageType, 'placeholderPath'));
    }

    /**
     * Get image path
     *
     * @param string $imageType
     * @param string $fileName
     * @return string
     * @throws \Exception
     */
    private function getImagePath($imageType, $fileName)
    {
        return $this->getFilePath($this->getImageTypeData($imageType, 'path'), $fileName);
    }

    /**
     * Get image type data
     *
     * @param string $imageType
     * @param string $fieldName
     * @return mixed
     * @throws \Exception
     */
    private function getImageTypeData($imageType, $fieldName)
    {
        if (!isset($this->imageTypes[$imageType])) {
            throw new \Exception(
                'Unknown image type: ' . $imageType
            );
        }
        return $this->imageTypes[$imageType][$fieldName];
    }

    /**
     * Copy image and resize
     *
     * @param string $fileName
     * @param string $path
     * @param int $size
     * @return void
     */
    private function copyAndResize($fileName, $path, $size)
    {
        $this->mediaDirectory->copyFile(
            $this->getFilePath(self::IMAGE_PATH, $fileName),
            $path
        );
        $filePath = $this->mediaDirectory->getAbsolutePath($path);

        $imageProcessor = $this->imageProcessorFactory->create($filePath);
        $imageProcessor->keepAspectRatio(true);
        $imageProcessor->keepFrame(true);
        $imageProcessor->keepTransparency(true);
        $imageProcessor->backgroundColor([255, 255, 255]);
        $imageProcessor->constrainOnly(true);
        $imageProcessor->quality(80);
        $imageProcessor->resize($size);
        $imageProcessor->save();
    }

    /**
     * Get file path
     *
     * @param string $path
     * @param string $fileName
     * @return string
     */
    private function getFilePath($path, $fileName)
    {
        return rtrim($path, '/') . '/' . ltrim($fileName, '/');
    }
}
