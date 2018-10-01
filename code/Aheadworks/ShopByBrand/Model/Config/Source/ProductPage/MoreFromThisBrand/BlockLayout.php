<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Config\Source\ProductPage\MoreFromThisBrand;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class BlockLayout
 * @package Aheadworks\ShopByBrand\Model\Config\Source\ProductPage\MoreFromThisBrand
 */
class BlockLayout implements OptionSourceInterface
{
    /**
     * 'Products aligned in single row' option
     */
    const SINGLE_ROW = 'single_row';

    /**
     * 'Products aligned in multiple rows' option
     */
    const MULTIPLE_ROWS = 'multiple_rows';

    /**
     * 'Slider' option
     */
    const SLIDER = 'slider';

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
                    'value' => self::SINGLE_ROW,
                    'label' => __('Products aligned in single row')
                ],
                [
                    'value' => self::MULTIPLE_ROWS,
                    'label' => __('Products aligned in multiple rows')
                ],
                [
                    'value' => self::SLIDER,
                    'label' => __('Slider')
                ]
            ];
        }
        return $this->options;
    }
}
