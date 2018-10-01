<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Brand\Website;

use Magento\Framework\App\ResourceConnection;
use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class SaveHandler
 * @package Aheadworks\ShopByBrand\Model\Brand\Website
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $entityId = (int)$entity->getBrandId();
        $websiteIds = $entity->getWebsiteIds();
        $websiteIdsOrig = $this->getWebsiteIds($entityId);

        $toInsert = array_diff($websiteIds, $websiteIdsOrig);
        $toDelete = array_diff($websiteIdsOrig, $websiteIds);

        $connection = $this->getConnection();
        $tableName = $this->resourceConnection->getTableName('aw_sbb_brand_website');

        if ($toInsert) {
            $data = [];
            foreach ($toInsert as $websiteId) {
                $data[] = [
                    'brand_id' => (int)$entityId,
                    'website_id' => (int)$websiteId,
                ];
            }
            $connection->insertMultiple($tableName, $data);
        }
        if (count($toDelete)) {
            $connection->delete(
                $tableName,
                ['brand_id = ?' => $entityId, 'website_id IN (?)' => $toDelete]
            );
        }
        return $entity;
    }

    /**
     * Get website IDs to which entity is assigned
     *
     * @param int $entityId
     * @return array
     */
    private function getWebsiteIds($entityId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->resourceConnection->getTableName('aw_sbb_brand_website'), 'website_id')
            ->where('brand_id = :id');
        return $connection->fetchCol($select, ['id' => $entityId]);
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
}
