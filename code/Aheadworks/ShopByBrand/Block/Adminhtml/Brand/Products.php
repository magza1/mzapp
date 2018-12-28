<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Block\Adminhtml\Brand;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Registry;
use Aheadworks\ShopByBrand\Api\BrandRepositoryInterface;
use Aheadworks\ShopByBrand\Model\ResourceModel\Product\Collection;
use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Directory\Model\Currency;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Products
 * @package Aheadworks\ShopByBrand\Block\Adminhtml\Brand
 */
class Products extends Extended
{
    /**
     * @var Registry
     */
    private $coreRegistry = null;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var Visibility
     */
    private $visibility;

    /**
     * @var BrandRepositoryInterface
     */
    private $brandRepository;

    /**
     * @var Collection
     */
    private $productCollection;

    /**
     * @var array
     */
    private $products;

    /**
     * @var BrandInterface|null
     */
    private $brand;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param ProductFactory $productFactory
     * @param Registry $coreRegistry
     * @param BrandRepositoryInterface $brandRepository
     * @param Collection $productCollection
     * @param Visibility $visibility
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        ProductFactory $productFactory,
        Registry $coreRegistry,
        BrandRepositoryInterface $brandRepository,
        Collection $productCollection,
        Visibility $visibility,
        array $data = []
    ) {
        $this->productFactory = $productFactory;
        $this->coreRegistry = $coreRegistry;
        $this->brandRepository = $brandRepository;
        $this->productCollection = $productCollection;
        $this->visibility = $visibility;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('aw_brand_products');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    /**
     * @return bool|BrandInterface
     */
    public function getBrand()
    {
        $brandId = (int)$this->getRequest()->getParam('brand_id', 0);
        if (!$this->brand && $brandId) {
            $this->brand = $this->brandRepository->get($brandId);
            return $this->brand;
        }
        return $this->brand;
    }

    /**
     * {@inheritdoc}
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_brand') {
            $productIds = $this->getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } elseif (!empty($productIds)) {
                $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
            }
        } elseif ($column->getId() == 'position_in_brand') {
            $this->addFilterByPosition($column->getFilter()->getValue());
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        if ($this->getBrand()) {
            $this->setDefaultFilter(['in_brand' => 1]);
        }
        $collection = $this->productFactory->create()->getCollection(
        )->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'sku'
        )->addAttributeToSelect(
            'price'
        )->joinAttribute(
            'visibility',
            'catalog_product/visibility',
            'entity_id',
            null,
            'inner'
        );
        $this->addAdditionalProducts($collection);
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_brand',
            [
                'type' => 'checkbox',
                'name' => 'in_brand',
                'values' => $this->getSelectedProducts(),
                'index' => 'entity_id',
                'header_css_class' => 'col-select col-massaction',
                'column_css_class' => 'col-select col-massaction'
            ]
        );

        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'name',
            ['header' => __('Name'),
                'index' => 'name']
        );

        $this->addColumn(
            'sku',
            ['header' => __('SKU'),
                'index' => 'sku']
        );

        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type' => 'currency',
                'currency_code' => (string)$this->_scopeConfig->getValue(
                    Currency::XML_PATH_CURRENCY_BASE,
                    ScopeInterface::SCOPE_STORE
                ),
                'index' => 'price'
            ]
        );

        $this->addColumn(
            'visibility',
            [
                'header' => __('Visibility'),
                'index' => 'visibility',
                'type' => 'options',
                'options' => $this->visibility->getOptionArray(),
                'header_css_class' => 'col-visibility',
                'column_css_class' => 'col-visibility'
            ]
        );

        $this->addColumn(
            'position_in_brand',
            [
                'header' => __('Position'),
                'type' => 'number',
                'index' => 'position_in_brand',
                'editable' => true
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @param Column $column
     * @return $this
     */
    protected function _setCollectionOrder($column)
    {
        $collection = $this->getCollection();
        if ($collection && $column->getIndex() == 'position_in_brand') {
            $collection->getSelect()->order('position_in_brand ' . strtoupper($column->getDir()));
            return $this;
        } else {
            return parent::_setCollectionOrder($column);
        }
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/productsGrid', ['_current' => true]);
    }

    /**
     * @return array
     */
    private function getSelectedProducts()
    {
        if ($this->products === null) {
            $brand = $this->getBrand();
            if (!$brand) {
                return [];
            }
            $this->products = $this->productCollection->getBrandProductsIds($brand);
        }
        return $this->products;
    }

    /**
     * @param mixed $collection
     * @throws \Exception
     */
    private function addAdditionalProducts($collection)
    {
        if ($this->getBrand()) {
            $this->productCollection->addAdditionalProducts(
                $collection,
                $this->getBrand()->getBrandId()
            );
        }
    }

    /**
     * @param array $value
     */
    private function addFilterByPosition($value)
    {
        if (is_array($value)) {
            if (isset($value['from'])) {
                $this->getCollection()->getSelect()->where(
                    'IFNULL(additional_brand_products.position_in_brand, '
                    . BrandInterface::DEFAULT_POSITION . ') >= ' . $value['from']
                );
            }
            if (isset($value['to'])) {
                $this->getCollection()->getSelect()->where(
                    'IFNULL(additional_brand_products.position_in_brand, '
                    . BrandInterface::DEFAULT_POSITION . ') <= ' . $value['to']
                );
            }
        }
    }
}
