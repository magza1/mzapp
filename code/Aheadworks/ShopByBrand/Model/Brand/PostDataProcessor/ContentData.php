<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Brand\PostDataProcessor;

use Aheadworks\ShopByBrand\Model\Brand\PostDataProcessorInterface;
use Magento\Framework\Stdlib\BooleanUtils;

/**
 * Class ContentData
 * @package Aheadworks\ShopByBrand\Model\Brand\PostDataProcessor
 */
class ContentData implements PostDataProcessorInterface
{
    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * @param BooleanUtils $booleanUtils
     */
    public function __construct(BooleanUtils $booleanUtils)
    {
        $this->booleanUtils = $booleanUtils;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareEntityData($data)
    {
        if (isset($data['content'])) {
            foreach ($data['content'] as $index => $descriptionData) {
                $isRemoved = $this->booleanUtils->toBoolean($descriptionData['removed']);
                if ($isRemoved) {
                    unset($data['content'][$index]);
                }
            }
        }
        return $data;
    }
}
