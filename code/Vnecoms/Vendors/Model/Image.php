<?php

namespace Vnecoms\Vendors\Model;

use Magento\Framework\Image as MagentoImage;

class Image extends \Magento\Catalog\Model\Product\Image
{
    const VENDOR_BASE_MEDIA_PATH = 'ves_vendors/media';
    
    /**
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product\Media\Config $catalogProductMediaConfig
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDatabase
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Image\Factory $imageFactory
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\View\FileSystem $viewFileSystem
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Media\Config $catalogProductMediaConfig,
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDatabase,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\Factory $imageFactory,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\View\FileSystem $viewFileSystem,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $storeManager,
            $catalogProductMediaConfig,
            $coreFileStorageDatabase,
            $filesystem,
            $imageFactory,
            $assetRepo,
            $viewFileSystem,
            $scopeConfig
        );
        $result = $this->_mediaDirectory->create($this->getBaseMediaPath());
    }

    /**
     * Get base media path
     *
     * @return string;
     */
    public function getBaseMediaPath()
    {
        $path = $this->getData('base_media_path');
        if (!$path) {
            return self::VENDOR_BASE_MEDIA_PATH;
        }
        
        return $path;
    }

    /**
     * (non-PHPdoc)
     * @see \Magento\Catalog\Model\Product\Image::setBaseFile()
     */
    public function setBaseFile($file)
    {
        $this->_isBaseFilePlaceholder = false;

        if ($file && 0 !== strpos($file, '/', 0)) {
            $file = '/' . $file;
        }
        $baseDir = $this->getBaseMediaPath();

        if ($file) {
            if (!$this->_fileExists($baseDir . $file) || !$this->_checkMemory($baseDir . $file)) {
                $file = null;
            }
        }
        if (!$file) {
            $this->_isBaseFilePlaceholder = true;
            // check if placeholder defined in config
            $isConfigPlaceholder = $this->_scopeConfig->getValue(
                "catalog/placeholder/{$this->getDestinationSubdir()}_placeholder",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $configPlaceholder = '/placeholder/' . $isConfigPlaceholder;
            if (!empty($isConfigPlaceholder) && $this->_fileExists($baseDir . $configPlaceholder)) {
                $file = $configPlaceholder;
            } else {
                $this->_newFile = true;
                return $this;
            }
        }

        $baseFile = $baseDir . $file;

        if (!$file || !$this->_mediaDirectory->isFile($baseFile)) {
            throw new \Exception(__('We can\'t find the image file.'));
        }

        $this->_baseFile = $baseFile;

        // build new filename (most important params)
        $path = [
            $this->getBaseMediaPath(),
            'cache',
            $this->_storeManager->getStore()->getId(),
        ];
        if (!empty($this->_width) || !empty($this->_height)) {
            $path[] = "{$this->_width}x{$this->_height}";
        }

        // add misk params as a hash
        $miscParams = [
            ($this->_keepAspectRatio ? '' : 'non') . 'proportional',
            ($this->_keepFrame ? '' : 'no') . 'frame',
            ($this->_keepTransparency ? '' : 'no') . 'transparency',
            ($this->_constrainOnly ? 'do' : 'not') . 'constrainonly',
            $this->_rgbToString($this->_backgroundColor),
            'angle' . $this->_angle,
            'quality' . $this->_quality,
        ];

        // if has watermark add watermark params to hash
        if ($this->getWatermarkFile()) {
            $miscParams[] = $this->getWatermarkFile();
            $miscParams[] = $this->getWatermarkImageOpacity();
            $miscParams[] = $this->getWatermarkPosition();
            $miscParams[] = $this->getWatermarkWidth();
            $miscParams[] = $this->getWatermarkHeight();
        }

        $path[] = md5(implode('_', $miscParams));

        // append prepared filename
        $this->_newFile = implode('/', $path) . $file;
        // the $file contains heading slash

        return $this;
    }
}
