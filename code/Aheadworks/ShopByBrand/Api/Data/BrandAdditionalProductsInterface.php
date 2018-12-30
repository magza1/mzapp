<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * BrandAdditionalProducts interface
 * @api
 */
interface BrandAdditionalProductsInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const PRODUCT_ID = 'product_id';
    const POSITION = 'position';
    /**#@-*/

    /**
     * Get product ID
     *
     * @return int|null
     */
    public function getProductId();

    /**
     * Set product ID
     *
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId);

    /**
     * Get position
     *
     * @return int
     */
    public function getPosition();

    /**
     * Set position
     *
     * @param int $position
     * @return $this
     */
    public function setPosition($position);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\ShopByBrand\Api\Data\BrandAdditionalProductsExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\ShopByBrand\Api\Data\BrandAdditionalProductsExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(BrandAdditionalProductsExtensionInterface $extensionAttributes);
}
