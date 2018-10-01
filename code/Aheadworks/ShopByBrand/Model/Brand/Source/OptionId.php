<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Brand\Source;

use Aheadworks\ShopByBrand\Model\ResourceModel\BrandAttributeOption as BrandOptionResource;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class OptionId
 * @package Aheadworks\ShopByBrand\Model\Brand\Source
 */
class OptionId implements OptionSourceInterface
{
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var BrandOptionResource
     */
    private $brandOptionResource;

    /**
     * @var array
     */
    private $options;

    /**
     * @var string
     */
    private $attributeCode;

    /**
     * @var int
     */
    private $currentOptionId;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     * @param BrandOptionResource $brandOptionResource
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        BrandOptionResource $brandOptionResource
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->brandOptionResource = $brandOptionResource;
    }

    /**
     * Set attribute code
     *
     * @param string $attributeCode
     * @return $this
     */
    public function setAttributeCode($attributeCode)
    {
        $this->attributeCode = $attributeCode;
        return $this;
    }

    /**
     * Set current selected option ID
     *
     * @param int $optionId
     * @return $this
     */
    public function setCurrentOptionId($optionId)
    {
        $this->currentOptionId = $optionId;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $attributeCode = $this->attributeCode;
        if (!$attributeCode) {
            return [];
        }
        if (!isset($this->options[$attributeCode])) {
            /** @var ProductAttributeInterface|AbstractAttribute $attribute */
            $attribute = $this->attributeRepository->get(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $attributeCode
            );
            /** @var \Magento\Eav\Model\Entity\Attribute\Source\Table $source */
            $source = $attribute->getSource();
            $options = $source->getAllOptions(true, true);
            $usedOptionIds = $this->brandOptionResource->getUsedOptionIds();
            $this->options[$attributeCode] = [];
            foreach ($options as $option) {
                $value = $option['value'];
                if (!in_array($value, $usedOptionIds)
                    || $this->currentOptionId && $value == $this->currentOptionId
                ) {
                    $this->options[$attributeCode][] = [
                        'value' => $value,
                        'label' => __($option['label'])
                    ];
                }
            }
        }
        return $this->options[$attributeCode];
    }
}
