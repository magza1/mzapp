<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsProduct\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeGroupRepositoryInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory as EavAttributeFactory;
use Magento\Eav\Api\Data\AttributeGroupInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory as GroupCollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filter\Translit;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Catalog\Ui\DataProvider\CatalogEavValidationRules;
use Magento\Ui\DataProvider\Mapper\FormElement as FormElementMapper;
use Magento\Ui\DataProvider\Mapper\MetaProperties as MetaPropertiesMapper;
use Magento\Ui\Component\Form\Element\Wysiwyg as WysiwygElement;
use Magento\Catalog\Model\Attribute\ScopeOverriddenValue;
use Magento\Framework\Locale\CurrencyInterface;
use Vnecoms\VendorsProduct\Model\Entity\Attribute\Set as VendorProductAttributeSet;
use Vnecoms\VendorsProduct\Model\ResourceModel\Entity\Attribute\CollectionFactory as CustomAttributeFactory;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;

/**
 * Class Eav
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Eav extends \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav
{
    /**
     * @var Vnecoms\VendorsProduct\Model\Entity\Attribute\Set
     */
    protected $_attributeSet;
    
    /**
     * @var \Vnecoms\VendorsProduct\Model\ResourceModel\Entity\Attribute\CollectionFactory
     */
    protected $_customAttributeCollectionFactory;
    
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $_attributeCollectionFactory;
    
    /**
     * @var array
     */
    private $bannedInputTypes = ['media_image'];
    
    /**
     * Constructor
     *
     * @param LocatorInterface $locator
     * @param CatalogEavValidationRules $catalogEavValidationRules
     * @param Config $eavConfig
     * @param RequestInterface $request
     * @param GroupCollectionFactory $groupCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param FormElementMapper $formElementMapper
     * @param MetaPropertiesMapper $metaPropertiesMapper
     * @param ProductAttributeGroupRepositoryInterface $attributeGroupRepository
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param EavAttributeFactory $eavAttributeFactory
     * @param Translit $translitFilter
     * @param ArrayManager $arrayManager
     * @param ScopeOverriddenValue $scopeOverriddenValue
     * @param DataPersistorInterface $dataPersistor
     * @param VendorProductAttributeSet $vendorProductAttributeSet
     * @param array $attributesToDisable
     * @param array $attributesToEliminate
     */
    public function __construct(
        LocatorInterface $locator,
        CatalogEavValidationRules $catalogEavValidationRules,
        Config $eavConfig,
        RequestInterface $request,
        GroupCollectionFactory $groupCollectionFactory,
        StoreManagerInterface $storeManager,
        FormElementMapper $formElementMapper,
        MetaPropertiesMapper $metaPropertiesMapper,
        ProductAttributeGroupRepositoryInterface $attributeGroupRepository,
        ProductAttributeRepositoryInterface $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        EavAttributeFactory $eavAttributeFactory,
        Translit $translitFilter,
        ArrayManager $arrayManager,
        ScopeOverriddenValue $scopeOverriddenValue,
        DataPersistorInterface $dataPersistor,
        VendorProductAttributeSet $vendorProductAttributeSet,
        CustomAttributeFactory $customAttributeCollectionFactory,
        AttributeCollectionFactory $attributeCollectionFactory,
        $attributesToDisable = [],
        $attributesToEliminate = []
    ) {
        parent::__construct(
            $locator,
            $catalogEavValidationRules,
            $eavConfig,
            $request,
            $groupCollectionFactory,
            $storeManager,
            $formElementMapper,
            $metaPropertiesMapper,
            $attributeGroupRepository,
            $attributeRepository,
            $searchCriteriaBuilder,
            $sortOrderBuilder,
            $eavAttributeFactory,
            $translitFilter,
            $arrayManager,
            $scopeOverriddenValue,
            $dataPersistor,
            $attributesToDisable,
            $attributesToEliminate
        );
        $vendorProductAttributeSet->load($this->getAttributeSetId(), 'parent_set_id');
        $this->_attributeSet = $vendorProductAttributeSet;
        $this->_customAttributeCollectionFactory = $customAttributeCollectionFactory;
        $this->_attributeCollectionFactory = $attributeCollectionFactory;
    }
    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        /* Get default form if the custom attribute set is not set*/
        if (!$this->_attributeSet->getId()) {
            return parent::modifyMeta($meta);
        }
        
        $product = $this->locator->getProduct();
        $sortOrder = 0;

        foreach ($this->_attributeSet->getGroupCollection() as $group) {
            $groupCode = 'vendor_product_group_'.$group->getId();
            
            /** @var $group \Vnecoms\VendorsProduct\Model\Entity\Attribute\Group*/
            $attributes = $this->getAttributeCollection($group->getId());
            
            $availableAttributes = [];
            foreach ($attributes as $key => $attribute) {
                $applyTo = $attribute->getApplyTo();
                if ($attribute->getIsVisible() && (empty($applyTo) || in_array($product->getTypeId(), $applyTo))
                ) {
                    $availableAttributes[] = $attribute;
                }
            }

            if ($availableAttributes) {
                $meta[$groupCode]['children'] = $this->getAttributesMeta($attributes, $groupCode);
                $meta[$groupCode]['arguments']['data']['config']['componentType'] = Fieldset::NAME;
                $meta[$groupCode]['arguments']['data']['config']['label'] = __('%1', $group->getAttributeGroupName());
                $meta[$groupCode]['arguments']['data']['config']['collapsible'] = true;
                $meta[$groupCode]['arguments']['data']['config']['dataScope'] = self::DATA_SCOPE_PRODUCT;
                $meta[$groupCode]['arguments']['data']['config']['sortOrder'] =
                    $sortOrder * self::SORT_ORDER_MULTIPLIER;
            }

            $sortOrder++;
        }

        return $meta;
    }

    /**
     * Get attribute collection
     * @param int $groupId
     * @return \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    public function getAttributeCollection($groupId)
    {
        $customAttrCollection = $this->_customAttributeCollectionFactory->create()->setAttributeGroupFilter(
            $groupId
        )->load();
        $attributeIds = $customAttrCollection->getColumnValues('attribute_id');
        $attrCollection = $this->_attributeCollectionFactory->create();
        $attrCollection->join(
            ['entity_attribute' => $attrCollection->getTable('ves_vendor_product_entity_attribute')],
            'entity_attribute.attribute_id = main_table.attribute_id AND attribute_group_id="'.$groupId.'"'
        )->addFieldToFilter(
            'main_table.attribute_id',
            ['in'=>$attributeIds]
        )->setOrder('sort_order', 'ASC')->addVisibleFilter()->load();
    
        return $attrCollection;
    }
    
    /**
     * Return current attribute set id
     *
     * @return int|null
     */
    private function getAttributeSetId()
    {
        return $this->locator->getProduct()->getAttributeSetId();
    }
    
    /**
     * Get attributes meta
     *
     * @param ProductAttributeInterface[] $attributes
     * @param string $groupCode
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAttributesMeta($attributes, $groupCode)
    {
        $meta = [];
    
        foreach ($attributes as $sortOrder => $attribute) {
            if (in_array($attribute->getFrontendInput(), $this->bannedInputTypes)) {
                continue;
            }
    
            if (in_array($attribute->getAttributeCode(), $this->attributesToEliminate)) {
                continue;
            }
    
            if (!($attributeContainer = $this->setupAttributeContainerMeta($attribute))) {
                continue;
            }
    
            $attributeContainer = $this->addContainerChildren($attributeContainer, $attribute, $groupCode, $sortOrder);
    
            $meta[static::CONTAINER_PREFIX . $attribute->getAttributeCode()] = $attributeContainer;
        }
    
        return $meta;
    }
}
