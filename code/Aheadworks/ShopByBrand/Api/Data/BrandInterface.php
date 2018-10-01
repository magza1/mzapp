<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Brand interface
 * @api
 */
interface BrandInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const BRAND_ID = 'brand_id';
    const ATTRIBUTE_ID = 'attribute_id';
    const ATTRIBUTE_CODE = 'attribute_code';
    const OPTION_ID = 'option_id';
    const NAME = 'name';
    const WEBSITE_IDS = 'website_ids';
    const URL_KEY = 'url_key';
    const LOGO = 'logo';
    const IS_FEATURED = 'is_featured';
    const CONTENT = 'content';
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
     * Get attribute ID
     *
     * @return int
     */
    public function getAttributeId();

    /**
     * Set attribute ID
     *
     * @param int $attributeId
     * @return $this
     */
    public function setAttributeId($attributeId);

    /**
     * Get attribute code
     *
     * @return string
     */
    public function getAttributeCode();

    /**
     * Set attribute code
     *
     * @param string $attributeCode
     * @return $this
     */
    public function setAttributeCode($attributeCode);

    /**
     * Get option ID
     *
     * @return int
     */
    public function getOptionId();

    /**
     * Set option ID
     *
     * @param int $optionId
     * @return $this
     */
    public function setOptionId($optionId);

    /**
     * Get brand name
     *
     * @return string
     */
    public function getName();

    /**
     * Set brand name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get website IDs
     *
     * @return int[]
     */
    public function getWebsiteIds();

    /**
     * Set website IDs
     *
     * @param int[] $websiteIds
     * @return $this
     */
    public function setWebsiteIds($websiteIds);

    /**
     * Get URL-key
     *
     * @return string
     */
    public function getUrlKey();

    /**
     * Set URL-key
     *
     * @param string $urlKey
     * @return $this
     */
    public function setUrlKey($urlKey);

    /**
     * Get logo
     *
     * @return string|null
     */
    public function getLogo();

    /**
     * Set logo
     *
     * @param string $logo
     * @return $this
     */
    public function setLogo($logo);

    /**
     * Get brand featured is featured flag
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsFeatured();

    /**
     * Set brand featured is featured flag
     *
     * @param bool $isFeatured
     * @return $this
     */
    public function setIsFeatured($isFeatured);

    /**
     * Get content
     *
     * @return \Aheadworks\ShopByBrand\Api\Data\BrandContentInterface[]
     */
    public function getContent();

    /**
     * Set content
     *
     * @param \Aheadworks\ShopByBrand\Api\Data\BrandContentInterface[] $content
     * @return $this
     */
    public function setContent($content);

    /**
     * Get meta title on storefront
     *
     * @return string
     */
    public function getMetaTitle();

    /**
     * Set meta title on storefront
     *
     * @param string $metaTitle
     * @return $this
     */
    public function setMetaTitle($metaTitle);

    /**
     * Get meta description on storefront
     *
     * @return string
     */
    public function getMetaDescription();

    /**
     * Set meta description on storefront
     *
     * @param string $metaDescription
     * @return $this
     */
    public function setMetaDescription($metaDescription);

    /**
     * Get description on storefront
     *
     * @return string
     */
    public function getDescription();

    /**
     * Set description on storefront
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return BrandExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param BrandExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(BrandExtensionInterface $extensionAttributes);
}
