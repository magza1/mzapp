<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Brand\PostDataProcessor;

use Aheadworks\ShopByBrand\Model\Brand\PostDataProcessorInterface;

/**
 * Class BrandId
 * @package Aheadworks\ShopByBrand\Model\Brand\PostDataProcessor
 */
class BrandId implements PostDataProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function prepareEntityData($data)
    {
        if (empty($data['brand_id'])) {
            $data['brand_id'] = null;
        }
        return $data;
    }
}
