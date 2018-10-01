<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 * @package Aheadworks\ShopByBrand\Model
 */
class Config
{
    /**
     * Configuration path to brand product attribute code
     */
    const XML_PATH_BRAND_PRODUCT_ATTRIBUTE_CODE = 'aw_sbb/general/brand_product_attribute';

    /**
     * Configuration path to display brand info on product page
     */
    const XML_PATH_DISPLAY_PRODUCT_PAGE_BRAND_INFO = 'aw_sbb/product_page/display_brand_info';

    /**
     * Configuration path to display brand description on product page
     */
    const XML_PATH_DISPLAY_PRODUCT_PAGE_BRAND_DESCRIPTION = 'aw_sbb/product_page/display_brand_description';

    /**
     * Configuration path to enable more from this brand block
     */
    const XML_PATH_MFTB_BLOCK_ENABLED = 'aw_sbb/more_from_this_brand_block/enabled';

    /**
     * Configuration path to more from this brand block name
     */
    const XML_PATH_MFTB_BLOCK_NAME = 'aw_sbb/more_from_this_brand_block/block_name';

    /**
     * Configuration path to more from this brand block position
     */
    const XML_PATH_MFTB_BLOCK_POSITION = 'aw_sbb/more_from_this_brand_block/block_position';

    /**
     * Configuration path to more from this brand block layout
     */
    const XML_PATH_MFTB_BLOCK_LAYOUT = 'aw_sbb/more_from_this_brand_block/block_layout';

    /**
     * Configuration path to more from this brand block's max products to display option
     */
    const XML_PATH_MFTB_BLOCK_PRODUCTS_LIMIT = 'aw_sbb/more_from_this_brand_block/products_limit';

    /**
     * Configuration path to more from this brand block's display add to cart option
     */
    const XML_PATH_MFTB_BLOCK_ADD_TO_CART_ENABLED = 'aw_sbb/more_from_this_brand_block/display_add_to_cart';

    /**
     * Configuration path to more from this brand block's sort products by option
     */
    const XML_PATH_MFTB_BLOCK_SORT_PRODUCTS_BY = 'aw_sbb/more_from_this_brand_block/sort_by';

    /**
     * Configuration path to add noindex to pagination pages
     */
    const XML_PATH_NOINDEX_PAGINATION_PAGES = 'aw_sbb/seo/noindex_pagination_pages';

    /**
     * Configuration path to brand sitemap change frequency
     */
    const XML_PATH_SITEMAP_CHANGEFREQ = 'sitemap/aw_sbb/changefreq';

    /**
     * Configuration path to brand sitemap priority
     */
    const XML_PATH_SITEMAP_PRIORITY = 'sitemap/aw_sbb/priority';

    /**
     * Configuration path to add store code to url
     */
    const XML_PATH_ADD_STORE_CODE_IN_URL = 'web/url/use_store';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get brand product attribute code
     *
     * @return int|null
     */
    public function getBrandProductAttributeCode()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_BRAND_PRODUCT_ATTRIBUTE_CODE);
    }

    /**
     * Get product page brand info block position
     *
     * @return string
     */
    public function getProductPageBrandInfoBlockPosition()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DISPLAY_PRODUCT_PAGE_BRAND_INFO,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if display product page brand description
     *
     * @return bool
     */
    public function isDisplayProductPageBrandDescription()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DISPLAY_PRODUCT_PAGE_BRAND_DESCRIPTION,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if display more from this brand block
     *
     * @return bool
     */
    public function isDisplayMoreFromThisBrandBlock()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_MFTB_BLOCK_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get more from this brand block name
     *
     * @return string
     */
    public function getMoreFromThisBrandBlockName()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MFTB_BLOCK_NAME,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get more from this brand block position
     *
     * @return string
     */
    public function getMoreFromThisBrandBlockPosition()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MFTB_BLOCK_POSITION,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get more from this brand block layout
     *
     * @return string
     */
    public function getMoreFromThisBrandBlockLayout()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MFTB_BLOCK_LAYOUT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get more from this brand block products limit
     *
     * @return int
     */
    public function getMoreFromThisBrandBlockProductsLimit()
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_MFTB_BLOCK_PRODUCTS_LIMIT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if Add to Cart button displayed on more from this brand block
     *
     * @return bool
     */
    public function isMoreFromThisBrandBlockAddToCartEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_MFTB_BLOCK_ADD_TO_CART_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get more from this brand block products sorting
     *
     * @return string
     */
    public function getMoreFromThisBrandSortProductsBy()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MFTB_BLOCK_SORT_PRODUCTS_BY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get brand url suffix. Equals to catalog category url suffix
     *
     * @return string
     */
    public function getBrandUrlSuffix()
    {
        return $this->scopeConfig->getValue(
            CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get brand sitemap change frequency
     *
     * @param int $storeId
     * @return string
     */
    public function getSitemapChangeFrequency($storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SITEMAP_CHANGEFREQ,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get brand sitemap priority
     *
     * @param int $storeId
     * @return string
     */
    public function getSitemapPriority($storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SITEMAP_PRIORITY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if add store code to urls enabled
     *
     * @return bool
     */
    public function isAddStoreCodeToUrlsEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ADD_STORE_CODE_IN_URL,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }

    /**
     * Check if add noindex meta tag to brand pagination pages
     *
     * @return bool
     */
    public function isAddNoindexToPaginationPages()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_NOINDEX_PAGINATION_PAGES,
            ScopeInterface::SCOPE_STORE
        );
    }
}
