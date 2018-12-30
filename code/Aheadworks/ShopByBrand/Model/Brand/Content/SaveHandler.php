<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Brand\Content;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Api\Data\BrandContentInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class SaveHandler
 * @package Aheadworks\ShopByBrand\Model\Brand\Content
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
        $content = $entity->getContent() ? : [];

        $connection = $this->resourceConnection->getConnectionByName(
            $this->metadataPool->getMetadata(BrandInterface::class)->getEntityConnectionName()
        );
        $tableName = $this->resourceConnection->getTableName('aw_sbb_brand_content');

        $connection->delete($tableName, ['brand_id = ?' => $entityId]);
        $toInsert = [];
        /** @var BrandContentInterface $storeContent */
        foreach ($content as $storeContent) {
            $toInsert[] = [
                'brand_id' => $entityId,
                'store_id' => $storeContent->getStoreId(),
                'meta_title' => $storeContent->getMetaTitle(),
                'meta_description' => $storeContent->getMetaDescription(),
                'description' => $storeContent->getDescription()
            ];
        }
        if ($toInsert) {
            $connection->insertMultiple($tableName, $toInsert);
        }

        return $entity;
    }
}
