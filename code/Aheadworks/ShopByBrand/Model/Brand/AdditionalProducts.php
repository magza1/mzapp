<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Brand;

use Aheadworks\ShopByBrand\Api\Data\BrandAdditionalProductsInterface;
use Aheadworks\ShopByBrand\Api\Data\BrandAdditionalProductsExtensionInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

/**
 * Class AdditionalProducts
 * @package Aheadworks\ShopByBrand\Model\Brand
 */
class AdditionalProducts extends AbstractExtensibleObject implements BrandAdditionalProductsInterface
{
    /**
     * {@inheritdoc}
     */
    public function getProductId()
    {
        return $this->_get(self::PRODUCT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return $this->_get(self::POSITION);
    }

    /**
     * {@inheritdoc}
     */
    public function setPosition($position)
    {
        return $this->setData(self::POSITION, $position);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(BrandAdditionalProductsExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
