<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Brand\AttributeCode;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 * @package Aheadworks\ShopByBrand\Model\Brand\AttributeCode
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $attributeId = (int)$entity->getAttributeId();
        if ($attributeId) {
            $connection = $this->resourceConnection->getConnectionByName(
                $this->metadataPool->getMetadata(BrandInterface::class)->getEntityConnectionName()
            );
            $select = $connection->select()
                ->from($this->resourceConnection->getTableName('eav_attribute'), ['attribute_code'])
                ->where('attribute_id = ?', $attributeId);
            $attributeCode = $connection->fetchOne($select);
            if ($attributeCode) {
                $entity->setAttributeCode($attributeCode);
            }
        }
        return $entity;
    }
}
