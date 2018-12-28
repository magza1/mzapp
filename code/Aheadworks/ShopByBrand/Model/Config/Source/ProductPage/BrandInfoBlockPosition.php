<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Config\Source\ProductPage;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class BrandInfoBlockPosition
 * @package Aheadworks\ShopByBrand\Model\Config\Source\ProductPage
 */
class BrandInfoBlockPosition implements OptionSourceInterface
{
    /**
     * "Don't display" option
     */
    const DONT_DISPLAY = 'dont_display';

    /**
     * 'Before short description' position
     */
    const BEFORE_SHORT_DESCRIPTION = 'before_short_description';

    /**
     * 'After short description' position
     */
    const AFTER_SHORT_DESCRIPTION = 'after_short_description';

    /**
     * 'Before product options' position
     */
    const BEFORE_PRODUCT_OPTIONS = 'before_product_options';

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
                    'value' => self::DONT_DISPLAY,
                    'label' => __('Don\'t display')
                ],
                [
                    'value' => self::BEFORE_SHORT_DESCRIPTION,
                    'label' => __('Before short description')
                ],
                [
                    'value' => self::AFTER_SHORT_DESCRIPTION,
                    'label' => __('After short description')
                ],
                [
                    'value' => self::BEFORE_PRODUCT_OPTIONS,
                    'label' => __('Before product options')
                ]
            ];
        }
        return $this->options;
    }
}
