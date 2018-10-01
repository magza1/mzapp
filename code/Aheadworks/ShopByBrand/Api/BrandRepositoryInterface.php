<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Brand CRUD interface
 * @api
 */
interface BrandRepositoryInterface
{
    /**
     * Save brand
     *
     * @param \Aheadworks\ShopByBrand\Api\Data\BrandInterface $brand
     * @return \Aheadworks\ShopByBrand\Api\Data\BrandInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Aheadworks\ShopByBrand\Api\Data\BrandInterface $brand);

    /**
     * Retrieve brand
     *
     * @param int $brandId
     * @return \Aheadworks\ShopByBrand\Api\Data\BrandInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($brandId);

    /**
     * Retrieve brand by product ID
     *
     * @param int $productId
     * @return \Aheadworks\ShopByBrand\Api\Data\BrandInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByProductId($productId);

    /**
     * Retrieve brands matching the specified criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Aheadworks\ShopByBrand\Api\Data\BrandSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete brand
     *
     * @param \Aheadworks\ShopByBrand\Api\Data\BrandInterface $brand
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Aheadworks\ShopByBrand\Api\Data\BrandInterface $brand);

    /**
     * Delete brand by ID
     *
     * @param int $brandId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($brandId);
}
