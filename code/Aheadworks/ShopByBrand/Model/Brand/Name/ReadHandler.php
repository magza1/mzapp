<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Brand\Name;

use Magento\Framework\App\ResourceConnection;
use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 * @package Aheadworks\ShopByBrand\Model\Brand\Name
 */
class ReadHandler implements ExtensionInterface
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
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(MetadataPool $metadataPool, ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($entity, $arguments = [])
    {
        $optionId = (int)$entity->getOptionId();
        if ($optionId) {
            $connection = $this->resourceConnection->getConnectionByName(
                $this->metadataPool->getMetadata(BrandInterface::class)->getEntityConnectionName()
            );
            $select = $connection->select()
                ->from($this->resourceConnection->getTableName('eav_attribute_option_value'))
                ->where('option_id = ?', $optionId);

            $storeId = isset($arguments['store_id'])
                ? $arguments['store_id']
                : 0;
            if ($storeId != 0) {
                $select->where('store_id IN (?)', [0, $storeId]);
            } else {
                $select->where('store_id = ?', $storeId);
            }

            $valuesData = $connection->fetchAll($select);
            $namesPerStore = [];
            foreach ($valuesData as $valueRow) {
                $namesPerStore[$valueRow['store_id']] = $valueRow['value'];
            }
            $name = isset($namesPerStore[$storeId])
                ? $namesPerStore[$storeId]
                : $namesPerStore[0];

            $entity->setName($name);
        }
        return $entity;
    }
}
