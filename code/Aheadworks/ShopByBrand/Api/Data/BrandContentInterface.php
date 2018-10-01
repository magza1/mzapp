<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Brand content interface
 * @api
 */
interface BrandContentInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const BRAND_ID = 'brand_id';
    const STORE_ID = 'store_id';
    const META_TITLE = 'meta_title';
    const META_DESCRIPTION = 'meta_description';
    const DESCRIPTION = 'description';
    /**#@-*/

    /**
     * Get brand ID
     *
     * @return int|null
     */
    public function getBrandId();

    /**
     * Set brand ID
     *
     * @param int $brandId
     * @return $this
     */
    public function setBrandId($brandId);

    /**
     * Get store ID
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set store ID
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get meta title
     *
     * @return string
     */
    public function getMetaTitle();

    /**
     * Set meta title
     *
     * @param string $metaTitle
     * @return $this
     */
    public function setMetaTitle($metaTitle);

    /**
     * Get meta description
     *
     * @return string
     */
    public function getMetaDescription();

    /**
     * Set meta description
     *
     * @param string $metaDescription
     * @return $this
     */
    public function setMetaDescription($metaDescription);

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Set description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return BrandContentExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param BrandContentExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(BrandContentExtensionInterface $extensionAttributes);
}
