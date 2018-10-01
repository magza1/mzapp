<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Config\Source\ProductPage\MoreFromThisBrand;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class BlockPosition
 * @package Aheadworks\ShopByBrand\Model\Config\Source\ProductPage\MoreFromThisBrand
 */
class BlockPosition implements OptionSourceInterface
{
    /**
     * 'Before native Related Products block' position
     */
    const BEFORE_RELATED_PRODUCTS = 'before_related_products';

    /**
     * 'After native Related Products block' position
     */
    const AFTER_RELATED_PRODUCTS = 'after_related_products';

    /**
     * 'Content top' position
     */
    const CONTENT_TOP = 'content_top';

    /**
     * 'Content bottom' position
     */
    const CONTENT_BOTTOM = 'content_bottom';

    /**
     * @var array
     */
    private $options;

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [
                [
                    'value' => self::BEFORE_RELATED_PRODUCTS,
                    'label' => __('Before native Related Products block')
                ],
                [
                    'value' => self::AFTER_RELATED_PRODUCTS,
                    'label' => __('After native Related Products block')
                ],
                [
                    'value' => self::CONTENT_TOP,
                    'label' => __('Content top')
                ],
                [
                    'value' => self::CONTENT_BOTTOM,
                    'label' => __('Content bottom')
                ]
            ];
        }
        return $this->options;
    }
}
