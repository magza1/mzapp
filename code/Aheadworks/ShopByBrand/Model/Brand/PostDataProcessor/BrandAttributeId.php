<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Brand\PostDataProcessor;

use Aheadworks\ShopByBrand\Model\Brand\PostDataProcessorInterface;
use Aheadworks\ShopByBrand\Model\Config;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;

/**
 * Class BrandAttributeId
 * @package Aheadworks\ShopByBrand\Model\Brand\PostDataProcessor
 */
class BrandAttributeId implements PostDataProcessorInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @param Config $config
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     */
    public function __construct(
        Config $config,
        ProductAttributeRepositoryInterface $productAttributeRepository
    ) {
        $this->config = $config;
        $this->productAttributeRepository = $productAttributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareEntityData($data)
    {
        if (!$data['brand_id']) {
            $brandAttribute = $this->productAttributeRepository->get(
                $this->config->getBrandProductAttributeCode()
            );
            $data['attribute_id'] = $brandAttribute->getAttributeId();
        }
        return $data;
    }
}
