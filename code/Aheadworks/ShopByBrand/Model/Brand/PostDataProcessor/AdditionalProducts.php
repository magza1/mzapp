<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Brand\PostDataProcessor;

use Aheadworks\ShopByBrand\Model\Brand\PostDataProcessorInterface;
use Magento\Framework\Json\DecoderInterface;
use Aheadworks\ShopByBrand\Api\Data\BrandAdditionalProductsInterface;

/**
 * Class AdditionalProducts
 * @package Aheadworks\ShopByBrand\Model\Brand\PostDataProcessor
 */
class AdditionalProducts implements PostDataProcessorInterface
{
    /**
     * @var DecoderInterface
     */
    private $jsonDecoder;

    /**
     * @param DecoderInterface $jsonDecoder
     */
    public function __construct(DecoderInterface $jsonDecoder)
    {
        $this->jsonDecoder = $jsonDecoder;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareEntityData($data)
    {
        if (isset($data['brand_additional_products'])) {
            $data['brand_additional_products'] = $this->jsonDecoder->decode($data['brand_additional_products']);
            $newProductsData = [];
            foreach ($data['brand_additional_products'] as $key => $value) {
                $newProductsData[] = [
                    BrandAdditionalProductsInterface::PRODUCT_ID => $key,
                    BrandAdditionalProductsInterface::POSITION => $value
                ];
            }
            $data['brand_additional_products'] = $newProductsData;
        }
        return $data;
    }
}
