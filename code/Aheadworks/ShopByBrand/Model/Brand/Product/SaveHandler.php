<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Brand\Product;

use Magento\Framework\App\ResourceConnection;
use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Aheadworks\ShopByBrand\Model\ResourceModel\Product\Collection;
use Aheadworks\ShopByBrand\Api\Data\BrandAdditionalProductsInterface;

/**
 * Class SaveHandler
 * @package Aheadworks\ShopByBrand\Model\Brand\Product
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var Collection
     */
    private $productCollection;

    /**
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param Collection $productCollection
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        Collection $productCollection
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->productCollection = $productCollection;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        if (!$entity->getBrandAdditionalProducts()) {
            return $entity;
        }

        $entityId = (int)$entity->getBrandId();
        $brandProducts = $this->prepareAdditionalProducts($entity->getBrandAdditionalProducts());
        $origBrandProducts = $this->productCollection->getSelectedProductsPositions($entity);

        $toInsertToBrand = array_diff_key($brandProducts, $origBrandProducts);
        $toDeleteFromBrand = array_diff_key($origBrandProducts, $brandProducts);
        $changedPositionInBrand = array_diff_assoc($brandProducts, $origBrandProducts);

        $idsToDelete = array_merge(
            array_keys($toInsertToBrand),
            array_keys($toDeleteFromBrand),
            array_keys($changedPositionInBrand)
        );

        $toInsertToBrand = $toInsertToBrand + $changedPositionInBrand;
        $connection = $this->getConnection();
        $tableName = $this->resourceConnection->getTableName('aw_sbb_additional_products');

        if (count($idsToDelete)) {
            $connection->delete(
                $tableName,
                ['brand_id = ?' => $entityId, 'product_id IN (?)' => array_unique($idsToDelete)]
            );
        }

        if (count($toInsertToBrand)) {
            $data = [];
            foreach ($toInsertToBrand as $key => $value) {
                $data[] = [
                    'brand_id' => (int)$entityId,
                    'product_id' => (int)$key,
                    'position_in_brand' => (int)$value,
                    'state' => BrandInterface::PRODUCT_ADDED
                ];
            }
            $connection->insertMultiple($tableName, $data);
        }

        if (count($toDeleteFromBrand)) {
            $data = [];
            foreach ($toDeleteFromBrand as $key => $value) {
                $data[] = [
                    'brand_id' => (int)$entityId,
                    'product_id' => (int)$key,
                    'position_in_brand' => (int)$value,
                    'state' => BrandInterface::PRODUCT_REMOVED
                ];
            }
            $connection->insertMultiple($tableName, $data);
        }

        return $entity;
    }

    /**
     * Get connection
     *
     * @return AdapterInterface
     * @throws \Exception
     */
    private function getConnection()
    {
        return $this->resourceConnection->getConnectionByName(
            $this->metadataPool->getMetadata(BrandInterface::class)->getEntityConnectionName()
        );
    }

    /**
     * @param BrandAdditionalProductsInterface[] $data
     * @return array
     */
    private function prepareAdditionalProducts($data)
    {
        $prepared = [];

        /** @var BrandAdditionalProductsInterface $item */
        foreach ($data as $item) {
            $prepared[$item->getProductId()] = $item->getPosition();
        }
        return $prepared;
    }
}
