<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Config\Source\ProductPage\MoreFromThisBrand;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class SortBy
 * @package Aheadworks\ShopByBrand\Model\Config\Source\ProductPage\MoreFromThisBrand
 */
class SortBy implements OptionSourceInterface
{
    /**
     * 'Bestsellers' option
     */
    const BESTSELLERS = 'bestsellers';

    /**
     * 'Newest' option
     */
    const NEWEST = 'newest';

    /**
     * 'Price: from high to low' option
     */
    const PRICE_DESC = 'price_desc';

    /**
     * 'Price: from low to high' option
     */
    const PRICE_ASC = 'price_acs';

    /**
     * 'Random' option
     */
    const RANDOM = 'random';

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
                    'value' => self::BESTSELLERS,
                    'label' => __('Bestsellers')
                ],
                [
                    'value' => self::NEWEST,
                    'label' => __('Newest')
                ],
                [
                    'value' => self::PRICE_DESC,
                    'label' => __('Price: from high to low')
                ],
                [
                    'value' => self::PRICE_ASC,
                    'label' => __('Price: from low to high')
                ],
                [
                    'value' => self::RANDOM,
                    'label' => __('Random')
                ]
            ];
        }
        return $this->options;
    }
}
