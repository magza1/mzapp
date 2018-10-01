<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\ResourceModel\Product;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

/**
 * Class Collection
 * @package Aheadworks\ShopByBrand\Model\ResourceModel\Product
 */
class Collection extends ProductCollection
{
    /**
     * Add brand filter
     *
     * @param BrandInterface $brand
     * @return $this
     */
    public function addBrandFilter($brand)
    {
        $connection = $this->getConnection();
        $this->getSelect()
            ->join(
                ['eav_index_table_brand' => $this->getTable('catalog_product_index_eav')],
                implode(
                    ' AND ',
                    [
                        'eav_index_table_brand.entity_id = e.entity_id',
                        'eav_index_table_brand.value = ' . $brand->getOptionId(),
                        'eav_index_table_brand.store_id = ' . $this->getStoreId()
                    ]
                ),
                []
            )->join(
                ['attribute_table_brand' => $this->getTable('eav_attribute')],
                implode(
                    ' AND ',
                    [
                        'attribute_table_brand.attribute_id = eav_index_table_brand.attribute_id',
                        'attribute_table_brand.attribute_code = ' . $connection->quote($brand->getAttributeCode())
                    ]
                ),
                []
            );
        return $this;
    }

    /**
     * Add sorting by bestsellers
     *
     * @return $this
     */
    public function addSortingByBestsellers()
    {
        $qtyOrderedSelect = $this->getConnection()->select();
        $qtyOrderedSelect
            ->from(
                ['order_item_table' => $this->getTable('sales_order_item')],
                ['qty_ordered' => new \Zend_Db_Expr('SUM(order_item_table.qty_ordered)'), 'product_id']
            )
            ->group('order_item_table.product_id');
        $this->getSelect()
            ->joinLeft(
                ['qty_ordered_select' => $qtyOrderedSelect],
                'e.entity_id = qty_ordered_select.product_id',
                []
            )
            ->group('e.entity_id')
            ->order('SUM(qty_ordered_select.qty_ordered) DESC');
        return $this;
    }

    /**
     * Add sorting by newest
     *
     * @return $this
     */
    public function addSortingByNewest()
    {
        $this->getSelect()->order('e.updated_at DESC');
        return $this;
    }

    /**
     * Add sorting by price ascending
     *
     * @return $this
     */
    public function addSortingByPriceAsc()
    {
        $this->addOrder('price', self::SORT_ORDER_ASC);
        return $this;
    }

    /**
     * Add sorting by price descending
     *
     * @return $this
     */
    public function addSortingByPriceDesc()
    {
        $this->addOrder('price');
        return $this;
    }

    /**
     * Add random sorting
     *
     * @return $this
     */
    public function addRandomSorting()
    {
        $this->getSelect()->orderRand('e.entity_id');
        return $this;
    }
}
