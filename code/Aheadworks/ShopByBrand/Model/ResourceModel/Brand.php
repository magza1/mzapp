<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\ResourceModel;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Class Brand
 * @package Aheadworks\ShopByBrand\Model\ResourceModel
 */
class Brand extends AbstractDb
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param Context $context
     * @param MetadataPool $metadataPool
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        MetadataPool $metadataPool,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->metadataPool = $metadataPool;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        return $this->_resources->getConnectionByName(
            $this->metadataPool->getMetadata(BrandInterface::class)->getEntityConnectionName()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('aw_sbb_brand', 'brand_id');
    }

    /**
     * Get brand ID by URL Key
     *
     * @param string $urlKey
     * @return bool|int
     * @throws LocalizedException
     */
    public function getBrandIdByUrlKey($urlKey)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable(), 'brand_id')
            ->where('url_key = :url_key');
        $brandId = $connection->fetchOne($select, ['url_key' => $urlKey]);
        return $brandId ? (int)$brandId : false;
    }

    /**
     * Get brand ID by product ID and attribute code
     *
     * @param int $productId
     * @param string $attributeCode
     * @param StoreInterface $store
     * @return bool|int
     * @throws LocalizedException
     */
    public function getBrandIdByProductIdAndAttributeCode($productId, $attributeCode, $store)
    {
        $connection = $this->getConnection();
        $mainTable = $this->getMainTable();
        $select = $connection->select()
            ->from($mainTable, 'brand_id')
            ->join(
                ['website_linkage_table' => $this->_resources->getTableName('aw_sbb_brand_website')],
                implode(
                    ' AND ',
                    [
                        'website_linkage_table.brand_id = ' . $mainTable . '.brand_id',
                        'website_linkage_table.website_id = ' . $store->getWebsiteId()
                    ]
                ),
                []
            )
            ->join(
                ['attribute_table' => $this->_resources->getTableName('eav_attribute')],
                implode(
                    ' AND ',
                    [
                        'attribute_table.attribute_id = ' . $mainTable . '.attribute_id',
                        'attribute_table.attribute_code = ' . $connection->quote($attributeCode)
                    ]
                ),
                []
            )
            ->join(
                ['eav_index_table' => $this->_resources->getTableName('catalog_product_index_eav')],
                implode(
                    ' AND ',
                    [
                        'eav_index_table.value = ' . $mainTable . '.option_id',
                        'eav_index_table.store_id = ' . $store->getId(),
                        'eav_index_table.entity_id = ' . $productId
                    ]
                ),
                []
            )
            ->group('eav_index_table.entity_id');

        $brandId = $connection->fetchOne($select);
        return $brandId ? (int)$brandId : false;
    }
}
