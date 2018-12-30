<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Block\Widget;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Api\Data\BrandSearchResultsInterface;
use Aheadworks\ShopByBrand\Api\BrandRepositoryInterface;
use Aheadworks\ShopByBrand\Model\Brand;
use Aheadworks\ShopByBrand\Model\Config;
use Aheadworks\ShopByBrand\Model\Url;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;

/**
 * Class ListBrand
 *
 * @method string getTitle()
 *
 * @package Aheadworks\ShopByBrand\Block\Widget
 */
class ListBrand extends \Magento\Framework\View\Element\Template implements BlockInterface, IdentityInterface
{
    /**
     * Default value whether show brand name or not
     */
    const DEFAULT_SHOW_NAME = false;

    /**
     * @var BrandRepositoryInterface
     */
    private $brandRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var BrandSearchResultsInterface|null
     */
    private $searchResults = null;

    /**
     * @param Context $context
     * @param BrandRepositoryInterface $brandRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param Config $config
     * @param Url $url
     * @param array $data
     */
    public function __construct(
        Context $context,
        BrandRepositoryInterface $brandRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        Config $config,
        Url $url,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->brandRepository = $brandRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->config = $config;
        $this->url = $url;
    }

    /**
     * Get brands
     *
     * @return BrandInterface[]
     */
    public function getBrands()
    {
        return $this->getSearchResults()->getItems();
    }

    /**
     * Check if show brand name
     *
     * @return bool
     */
    public function isShowName()
    {
        return $this->getData('show_name') ? : self::DEFAULT_SHOW_NAME;
    }

    /**
     * Get brands search results
     *
     * @return BrandSearchResultsInterface|null
     */
    private function getSearchResults()
    {
        if (!$this->searchResults) {
            $nameOrder = $this->sortOrderBuilder
                ->setField(BrandInterface::NAME)
                ->setAscendingDirection()
                ->create();
            $this->searchCriteriaBuilder
                ->addSortOrder($nameOrder)
                ->addFilter(
                    BrandInterface::ATTRIBUTE_CODE,
                    $this->config->getBrandProductAttributeCode()
                )
                ->addFilter(
                    'website_id',
                    [$this->_storeManager->getWebsite()->getId()]
                );
            $this->searchResults = $this->brandRepository->getList(
                $this->searchCriteriaBuilder->create()
            );
        }
        return $this->searchResults;
    }

    /**
     * Check if there are featured brands
     *
     * @return bool
     */
    public function hasFeaturedBrands()
    {
        foreach ($this->getBrands() as $brand) {
            if ($brand->getIsFeatured()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get brand page url
     *
     * @param BrandInterface $brand
     * @return string
     */
    public function getBrandUrl($brand)
    {
        return $this->url->getBrandUrl($brand);
    }

    /**
     * Get brand logo image url
     *
     * @param BrandInterface $brand
     * @return string
     */
    public function getLogoUrl($brand)
    {
        return $this->url->getLogoUrl($brand->getLogo(), 'small_image');
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if ($this->getSearchResults()->getTotalCount()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        return [Brand::CACHE_TAG];
    }
}
