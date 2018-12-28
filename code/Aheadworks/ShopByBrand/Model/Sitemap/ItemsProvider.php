<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Sitemap;

use Aheadworks\ShopByBrand\Api\BrandRepositoryInterface;
use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Model\Config;
use Aheadworks\ShopByBrand\Model\Url;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ItemsProvider
 * @package Aheadworks\ShopByBrand\Model\Sitemap
 */
class ItemsProvider
{
    /**
     * @var BrandRepositoryInterface
     */
    private $brandRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param BrandRepositoryInterface $brandRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Config $config
     * @param Url $url
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        BrandRepositoryInterface $brandRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Config $config,
        Url $url,
        StoreManagerInterface $storeManager
    ) {
        $this->brandRepository = $brandRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->config = $config;
        $this->url = $url;
        $this->storeManager = $storeManager;
    }

    /**
     * Get brand sitemap items
     *
     * @param int $storeId
     * @return DataObject
     */
    public function getBrandItems($storeId)
    {
        $items = [];
        foreach ($this->getBrands($storeId) as $brand) {
            $items[$brand->getBrandId()] = new DataObject(
                [
                    'id' => $brand->getBrandId(),
                    'url' => $this->url->getBrandPath($brand),
                    'updated_at' => $this->getCurrentDateTime()
                ]
            );
        }
        return new DataObject(
            [
                'changefreq' => $this->config->getSitemapChangeFrequency($storeId),
                'priority' => $this->config->getSitemapPriority($storeId),
                'collection' => $items
            ]
        );
    }

    /**
     * Retrieves list of brands
     *
     * @param int $storeId
     * @return BrandInterface[]
     */
    private function getBrands($storeId)
    {
        $store = $this->storeManager->getStore($storeId);
        $this->searchCriteriaBuilder
            ->addFilter(
                BrandInterface::ATTRIBUTE_CODE,
                $this->config->getBrandProductAttributeCode()
            )
            ->addFilter(
                'website_id',
                [$store->getWebsiteId()]
            );
        return $this->brandRepository
            ->getList($this->searchCriteriaBuilder->create())
            ->getItems();
    }

    /**
     * Current date/time
     *
     * @return string
     */
    private function getCurrentDateTime()
    {
        return (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
    }
}
