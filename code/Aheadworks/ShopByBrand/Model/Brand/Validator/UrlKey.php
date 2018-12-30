<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Brand\Validator;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Model\ResourceModel\Validator\UrlKeyIsUnique;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Class UrlKey
 * @package Aheadworks\ShopByBrand\Model\Brand\Validator
 */
class UrlKey extends AbstractValidator
{
    /**
     * @var UrlKeyIsUnique
     */
    private $uniquenessValidator;

    /**
     * @param UrlKeyIsUnique $uniquenessValidator
     */
    public function __construct(UrlKeyIsUnique $uniquenessValidator)
    {
        $this->uniquenessValidator = $uniquenessValidator;
    }

    /**
     * Returns true if and only if brand URL-key meets the validation requirements
     *
     * @param BrandInterface $brand
     * @return bool
     */
    public function isValid($brand)
    {
        $this->_clearMessages();

        if (!\Zend_Validate::is($brand->getUrlKey(), 'NotEmpty')) {
            $this->_addMessages(['Url key is required.']);
            return false;
        }
        if (preg_match('/^[0-9]+$/', $brand->getUrlKey())) {
            $this->_addMessages(['Url key consists of numbers.']);
            return false;
        }
        if (!preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $brand->getUrlKey())) {
            $this->_addMessages(['Url key contains disallowed symbols.']);
            return false;
        }
        if (!$this->uniquenessValidator->validate($brand)) {
            $this->_addMessages(['This URL-Key is already assigned to another brand.']);
        }

        return empty($this->getMessages());
    }
}
