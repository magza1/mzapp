<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Brand\Validator;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Class DefaultValidator
 * @package Aheadworks\ShopByBrand\Model\Brand\Validator
 */
class DefaultValidator extends AbstractValidator
{
    /**
     * Returns true if and only if brand entity meets the validation requirements
     *
     * @param BrandInterface $brand
     * @return bool
     */
    public function isValid($brand)
    {
        $this->_clearMessages();

        if (!\Zend_Validate::is($brand->getOptionId(), 'NotEmpty')) {
            $this->_addMessages(['Attribute option is required.']);
            return false;
        }

        return empty($this->getMessages());
    }
}
