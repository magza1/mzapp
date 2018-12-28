<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Brand\Validator;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Class Content
 * @package Aheadworks\ShopByBrand\Model\Brand\Validator
 */
class Content extends AbstractValidator
{
    /**
     * Returns true if and only if brand content data meets the validation requirements
     *
     * @param BrandInterface $brand
     * @return bool
     */
    public function isValid($brand)
    {
        $this->_clearMessages();

        $isAllStoreViewsDataPresents = false;
        $contentStoreIds = [];
        if ($brand->getContent()) {
            foreach ($brand->getContent() as $contentData) {
                if (!in_array($contentData->getStoreId(), $contentStoreIds)) {
                    array_push($contentStoreIds, $contentData->getStoreId());
                } else {
                    $this->_addMessages(['Duplicated store view in content data found.']);
                    return false;
                }
                if ($contentData->getStoreId() == 0) {
                    $isAllStoreViewsDataPresents = true;
                }

                if (!\Zend_Validate::is($contentData->getMetaTitle(), 'NotEmpty')) {
                    $this->_addMessages(['Meta title is required.']);
                }
                if (!\Zend_Validate::is($contentData->getMetaDescription(), 'NotEmpty')) {
                    $this->_addMessages(['Meta description is required.']);
                }

                $needle = \Aheadworks\ShopByBrand\Block\Widget\ListBrand::class;
                if (strstr($contentData->getDescription(), $needle) !== false) {
                    $this->_addMessages(
                        ['Make sure that brand description does not reference the brand itself.']
                    );
                }
            }
        }
        if (!$isAllStoreViewsDataPresents) {
            $this->_addMessages(
                ['Default values of content data (for All Store Views option) aren\'t set.']
            );
        }

        return empty($this->getMessages());
    }
}
