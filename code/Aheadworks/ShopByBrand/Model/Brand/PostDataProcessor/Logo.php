<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Brand\PostDataProcessor;

use Aheadworks\ShopByBrand\Model\Brand\PostDataProcessorInterface;

/**
 * Class Logo
 * @package Aheadworks\ShopByBrand\Model\Brand\PostDataProcessor
 */
class Logo implements PostDataProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function prepareEntityData($data)
    {
        if (isset($data['logo'])) {
            $imageData = $data['logo'];
            $data['logo'] = isset($imageData[0]['name'])
                ? $imageData[0]['name']
                : '';
        } else {
            $data['logo'] = null;
        }
        return $data;
    }
}
