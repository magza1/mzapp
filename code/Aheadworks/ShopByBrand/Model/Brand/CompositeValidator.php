<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Brand;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Framework\Validator\ValidatorInterface;

/**
 * Class CompositeValidator
 * @package Aheadworks\ShopByBrand\Model\Brand
 */
class CompositeValidator extends AbstractValidator
{
    /**
     * @var ValidatorInterface[]
     */
    private $validators;

    /**
     * @param array $validators
     */
    public function __construct($validators = [])
    {
        $this->validators = $validators;
    }

    /**
     * Returns true if and only if brand entity meets the validation requirements
     *
     * @param BrandInterface $brand
     * @return bool
     */
    public function isValid($brand)
    {
        $this->_clearMessages();
        foreach ($this->validators as $validator) {
            if (!$validator->isValid($brand)) {
                $this->_addMessages($validator->getMessages());
            }
        }
        return empty($this->getMessages());
    }
}
