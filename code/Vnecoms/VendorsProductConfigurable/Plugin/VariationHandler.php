<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsProductConfigurable\Plugin;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class PriceBackend
 *
 *  Make price validation optional for configurable product
 */
class VariationHandler extends \Magento\ConfigurableProduct\Model\Product\VariationHandler
{

    /**
     * @param \Magento\Catalog\Model\Product\Attribute\Backend\Price $subject
     * @param \Closure $proceed
     * @param @param \Magento\Catalog\Model\Product $parentProduct
     * @param array $productsData
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGenerateSimpleProducts(
        \Magento\ConfigurableProduct\Model\Product\VariationHandler $subject,
        \Closure $proceed,
        $parentProduct,
        $productsData
    ) {
        $object_manager = \Magento\Framework\App\ObjectManager::getInstance();
        $store = $object_manager->get('\Magento\Store\Model\StoreManagerInterface');

        $generatedProductIds = [];
        $productsData = $this->duplicateImagesForVariations($productsData);
        foreach ($productsData as $simpleProductData) {
            $newSimpleProduct = $this->productFactory->create();
            if (isset($simpleProductData['configurable_attribute'])) {
                $configurableAttribute = json_decode($simpleProductData['configurable_attribute'], true);
                unset($simpleProductData['configurable_attribute']);
            } else {
                throw new LocalizedException(__('Configuration must have specified attributes'));
            }
        
            $this->fillSimpleProductData(
                $newSimpleProduct,
                $parentProduct,
                array_merge($simpleProductData, $configurableAttribute)
            );
            $newSimpleProduct->setVendorId($parentProduct->getVendorId());
            $newSimpleProduct->setApproval($parentProduct->getApproval());
            $newSimpleProduct->setWebsiteIds([$store->getWebsite()->getId() => $store->getWebsite()->getId()]);
            $newSimpleProduct->save();
        
            $generatedProductIds[] = $newSimpleProduct->getId();
        }
        return $generatedProductIds;
    }
}
