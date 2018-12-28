<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Model\Brand\Image\Management as ImageManagement;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Url
 * @package Aheadworks\ShopByBrand\Model
 */
class Url
{
    /**
     * Route to brand page value
     */
    const ROUTE_TO_BRAND = 'brand';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ImageManagement
     */
    private $imageManagement;

    /**
     * @param UrlInterface $urlBuilder
     * @param StoreManagerInterface $storeManager
     * @param ImageManagement $imageManagement
     * @param Config $config
     */
    public function __construct(
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        ImageManagement $imageManagement,
        Config $config
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
        $this->imageManagement = $imageManagement;
        $this->config = $config;
    }

    /**
     * Get brand page url
     *
     * @param BrandInterface $brand
     * @param int|null $storeId
     * @return string
     */
    public function getBrandUrl($brand, $storeId = null)
    {
        $routeParams = ['_direct' => $this->getBrandPath($brand)];
        if ($storeId) {
            $this->urlBuilder->setScope($storeId);
            $routeParams['_scope'] = $storeId;
        }
        $url = $this->urlBuilder->getUrl(null, $routeParams);
        $this->urlBuilder->setScope(null);
        return $url;
    }

    /**
     * Get brand page path
     *
     * @param BrandInterface $brand
     * @return string
     */
    public function getBrandPath($brand)
    {
        return self::ROUTE_TO_BRAND . '/' . $brand->getUrlKey()
            . $this->config->getBrandUrlSuffix();
    }

    /**
     * Get logo image url
     *
     * @param string $imageName
     * @param string|null $imageType
     * @return string
     */
    public function getLogoUrl($imageName, $imageType = null)
    {
        if (!$imageType) {
            /** @var StoreInterface|Store $store */
            $store = $this->storeManager->getStore();
            return $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
                . $this->getFilePath(ImageManagement::IMAGE_PATH, $imageName);
        } else {
            $placeholderUrl = $this->imageManagement->getImagePlaceholderUrl($imageType);
            if (empty($imageName)) {
                return $placeholderUrl;
            }
            if (!$this->imageManagement->hasImage($imageType, $imageName)) {
                try {
                    $this->imageManagement->createImage($imageType, $imageName);
                } catch (\Exception $e) {
                    return $placeholderUrl;
                }
            }
            return $this->imageManagement->getImageUrl($imageType, $imageName);
        }
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
