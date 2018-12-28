<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\ResourceModel;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\App\ProductMetadataInterface;
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
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @param Context $context
     * @param MetadataPool $metadataPool
     * @param AttributeRepositoryInterface $attributeRepository
     * @param ProductMetadataInterface $productMetadata
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        MetadataPool $metadataPool,
        AttributeRepositoryInterface $attributeRepository,
        ProductMetadataInterface $productMetadata,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->metadataPool = $metadataPool;
        $this->attributeRepository = $attributeRepository;
        $this->productMetadata = $productMetadata;
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
                ['eav_index_table' => $this->getProductOptionSelect($productId, $attributeCode, $store)],
                'eav_index_table.option_id = ' . $mainTable . '.option_id',
                []
            );

        $brandId = $connection->fetchOne($select);
        if (!$brandId) {
            $brandId = $this->checkIfProductIsAdditional($productId);
        }
        return $brandId ? (int)$brandId : false;
    }

    /**
     * Product option sub query
     *
     * @param int $productId
     * @param string $attributeCode
     * @param StoreInterface $store
     * @return Select
     * @throws LocalizedException
     */
    private function getProductOptionSelect($productId, $attributeCode, $store)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(['attribute_table' => $this->_resources->getTableName('eav_attribute')], [])
            ->join(
                ['eav_index_table' => $this->_resources->getTableName('catalog_product_index_eav')],
                implode(
                    ' AND ',
                    [
                        'attribute_table.attribute_id = eav_index_table.attribute_id',
                        'eav_index_table.store_id = ' . $store->getId(),
                        'eav_index_table.entity_id = ' . $productId
                    ]
                ),
                []
            )
            ->join(
                ['e' => $this->_resources->getTableName('catalog_product_entity')],
                'e.entity_id = ' . $productId,
                []
            );

        if ($this->hasSourceIdInCatalogIndex()) {
            $entityLinkField = 'entity_id';
            $select
                ->joinLeft(
                    ['eav_parent_index_table' => $this->_resources->getTableName('catalog_product_index_eav')],
                    implode(
                        ' AND ',
                        [
                            'attribute_table.attribute_id = eav_parent_index_table.attribute_id',
                            'eav_parent_index_table.store_id = ' . $store->getId(),
                            'eav_parent_index_table.entity_id = ' . $productId,
                            'eav_parent_index_table.source_id = ' . $productId
                        ]
                    ),
                    []
                );
        } else {
            /* @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
            $attribute = $this->attributeRepository->get('catalog_product', $attributeCode);
            $attValueTableName = $attribute->getBackendTable();
            $entityLinkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();

            $select
                ->joinLeft(
                    ['eav_parent_index_table' => $attValueTableName],
                    implode(
                        ' AND ',
                        [
                            'attribute_table.attribute_id = eav_parent_index_table.attribute_id',
                            'eav_parent_index_table.' . $entityLinkField . ' = e.' . $entityLinkField
                        ]
                    ),
                    []
                );
        }

        $select
            ->columns(
                new \Zend_Db_Expr(
                    'CASE 
                        WHEN (e.type_id = "configurable" AND !ISNULL(eav_parent_index_table.' . $entityLinkField . ')) 
                            OR e.type_id = "grouped" OR e.type_id = "bundle" THEN eav_parent_index_table.value
                        WHEN (e.type_id = "configurable" AND ISNULL(eav_parent_index_table.' . $entityLinkField . ')) 
                            OR e.type_id = "simple" THEN eav_index_table.value
                        ELSE null
                    END AS "option_id"'
                )
            )
            ->where('attribute_table.attribute_code = ?', $attributeCode)
            ->limit(1);

        return $select;
    }

    /**
     * Check if the product index has the source_id field
     *
     * @return bool
     */
    private function hasSourceIdInCatalogIndex()
    {
        if (version_compare($this->productMetadata->getVersion(), '2.1.9', '>=')) {
            return true;
        }
        return false;
    }

    /**
     * Check if the product is added to brand as additional
     *
     * @param int $productId
     * @return bool|int
     */
    private function checkIfProductIsAdditional($productId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(
                ['main_table' => $this->getTable('aw_sbb_additional_products')],
                ['main_table.brand_id']
            )
            ->where('main_table.product_id = ' . $productId)
            ->where('main_table.state = ' . BrandInterface::PRODUCT_ADDED);

        $brandId = $connection->fetchOne($select);
        return $brandId ? (int)$brandId : false;
    }
}
