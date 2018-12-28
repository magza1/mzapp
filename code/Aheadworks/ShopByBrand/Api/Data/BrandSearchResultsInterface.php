<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for brand search results
 * @api
 */
interface BrandSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get brands list
     *
     * @return \Aheadworks\ShopByBrand\Api\Data\BrandInterface[]
     */
    public function getItems();

    /**
     * Set brands list
     *
     * @param \Aheadworks\ShopByBrand\Api\Data\BrandInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
