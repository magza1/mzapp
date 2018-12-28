<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Brand;

/**
 * Interface PostDataProcessorInterface
 * @package Aheadworks\ShopByBrand\Model\Brand
 */
interface PostDataProcessorInterface
{
    /**
     * Prepare entity data for save
     *
     * @param array $data
     * @return array
     */
    public function prepareEntityData($data);
}
