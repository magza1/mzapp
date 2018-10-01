<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Layer;

use Aheadworks\ShopByBrand\Api\BrandRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\Layer\CollectionFilterInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\RequestInterface;

/**
 * Class CollectionFilter
 * @package Aheadworks\ShopByBrand\Model\Layer
 */
class CollectionFilter implements CollectionFilterInterface
{
    /**
     * @var CatalogConfig
     */
    private $catalogConfig;

    /**
     * @var Visibility
     */
    private $productVisibility;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var BrandRepositoryInterface
     */
    private $brandRepository;

    /**
     * @param CatalogConfig $catalogConfig
     * @param Visibility $productVisibility
     * @param RequestInterface $request
     * @param BrandRepositoryInterface $brandRepository
     */
    public function __construct(
        CatalogConfig $catalogConfig,
        Visibility $productVisibility,
        RequestInterface $request,
        BrandRepositoryInterface $brandRepository
    ) {
        $this->catalogConfig = $catalogConfig;
        $this->productVisibility = $productVisibility;
        $this->request = $request;
        $this->brandRepository = $brandRepository;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function filter($collection, Category $category)
    {
        $collection
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addUrlRewrite()
            ->addStoreFilter()
            ->setVisibility($this->productVisibility->getVisibleInCatalogIds());
        $this->addBrandAttributeFilter($collection);
    }

    /**
     * Add brand attribute filter to product collection
     *
     * @param Collection $collection
     * @return void
     */
    private function addBrandAttributeFilter($collection)
    {
        $brandId = $this->request->getParam('brand_id');
        $brand = $this->brandRepository->get($brandId);
        $collection->addFieldToFilter(
            $brand->getAttributeCode(),
            $brand->getOptionId()
        );
    }
}
