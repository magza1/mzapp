<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Brand\Validator;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute as EavResource;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Class Attribute
 * @package Aheadworks\ShopByBrand\Model\Brand\Validator
 */
class Attribute extends AbstractValidator
{
    /**
     * @var EavResource
     */
    private $eavResource;

    /**
     * @var AttributeFactory
     */
    private $attributeFactory;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @param EavResource $eavResource
     * @param AttributeFactory $attributeFactory
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     */
    public function __construct(
        EavResource $eavResource,
        AttributeFactory $attributeFactory,
        ProductAttributeRepositoryInterface $productAttributeRepository
    ) {
        $this->eavResource = $eavResource;
        $this->attributeFactory = $attributeFactory;
        $this->productAttributeRepository = $productAttributeRepository;
    }

    /**
     * Returns true if and only if brand entity attribute ID meets the validation requirements
     *
     * @param BrandInterface $brand
     * @return bool
     */
    public function isValid($brand)
    {
        $this->_clearMessages();

        if (!\Zend_Validate::is($brand->getAttributeId(), 'NotEmpty')) {
            $this->_addMessages(['Attribute ID is required.']);
            return false;
        }

        $attribute = $this->getAttribute($brand->getAttributeId());
        if (!$this->isProductAttribute($attribute)) {
            $this->_addMessages(['Attribute entity type is invalid. Please, select a product attribute.']);
            return false;
        }
        if (!$attribute->getIsVisible()
            || !$attribute->getIsFilterable()
            || $attribute->getFrontendInput() != 'select'
        ) {
            $this->_addMessages(['Attribute is invalid.']);
            return false;
        }

        return empty($this->getMessages());
    }

    /**
     * Retrieve attribute instance by attribute ID
     *
     * @param int $attributeId
     * @return \Magento\Eav\Model\Entity\Attribute|ProductAttributeInterface
     */
    private function getAttribute($attributeId)
    {
        $attribute = $this->attributeFactory->create();
        $this->eavResource->load($attribute, $attributeId);
        return $attribute;
    }

    /**
     * Check if this is a product attribute
     *
     * @param AttributeInterface $attribute
     * @return bool
     */
    private function isProductAttribute($attribute)
    {
        try {
            $this->productAttributeRepository->get($attribute->getAttributeCode());
        } catch (NoSuchEntityException $e) {
            return false;
        }
        return true;
    }
}
