<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Layer;

use Aheadworks\ShopByBrand\Api\BrandRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\Layer\CollectionFilterInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\RequestInterface;
use Aheadworks\ShopByBrand\Model\ResourceModel\Product\Collection as BrandProductCollection;
use Aheadworks\ShopByBrand\Api\Data\BrandInterface;

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
     * @var BrandProductCollection
     */
    private $productCollection;

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
        BrandRepositoryInterface $brandRepository,
        BrandProductCollection $productCollection
    ) {
        $this->catalogConfig = $catalogConfig;
        $this->productVisibility = $productVisibility;
        $this->request = $request;
        $this->brandRepository = $brandRepository;
        $this->productCollection = $productCollection;
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
        $this->addBrandProductsFilter($collection);
    }

    /**
     * Add brand filter to product collection
     *
     * @param Collection $collection
     * @return void
     */
    private function addBrandProductsFilter($collection)
    {
        $brandId = $this->request->getParam('brand_id');
        $productIds = $this->getBrandProductsIds($brandId);
        $this->productCollection->addAdditionalProducts($collection, $brandId);

        $collection->addFieldToFilter(
            'entity_id',
            ['in' => $productIds]
        );
    }

    /**
     * @param int $brandId
     * @return array
     */
    private function getBrandProductsIds($brandId)
    {
        $brand = $this->brandRepository->get($brandId);
        $productIds = $this->productCollection->getBrandProductsIds($brand);

        if ($productIds && count($productIds)) {
            return $productIds;
        }
        return [0];
    }
}
