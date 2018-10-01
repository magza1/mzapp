<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Brand\Content;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Api\Data\BrandContentInterface;
use Aheadworks\ShopByBrand\Api\Data\BrandContentInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 * @package Aheadworks\ShopByBrand\Model\Brand\Content
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
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var BrandContentInterfaceFactory
     */
    private $brandContentFactory;

    /**
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param DataObjectHelper $dataObjectHelper
     * @param BrandContentInterfaceFactory $brandContentFactory
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        DataObjectHelper $dataObjectHelper,
        BrandContentInterfaceFactory $brandContentFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->brandContentFactory = $brandContentFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute($entity, $arguments = [])
    {
        $entityId = (int)$entity->getBrandId();
        if ($entityId) {
            $connection = $this->resourceConnection->getConnectionByName(
                $this->metadataPool->getMetadata(BrandInterface::class)->getEntityConnectionName()
            );
            $select = $connection->select()
                ->from($this->resourceConnection->getTableName('aw_sbb_brand_content'))
                ->where('brand_id = :id');
            $contentData = $connection->fetchAll($select, ['id' => $entityId]);

            $content = [];
            $metaTitle = null;
            $metaDescription = null;
            $description = null;
            foreach ($contentData as $data) {
                $contentEntity = $this->brandContentFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $contentEntity,
                    $data,
                    BrandContentInterface::class
                );
                $content[] = $contentEntity;

                if (isset($arguments['store_id']) && $data['store_id'] == $arguments['store_id']) {
                    list($metaTitle, $metaDescription, $description) = [
                        $data['meta_title'],
                        $data['meta_description'],
                        $data['description']
                    ];
                }
                if ($data['store_id'] == 0) {
                    if (!isset($arguments['store_id'])) {
                        list($metaTitle, $metaDescription, $description) = [
                            $data['meta_title'],
                            $data['meta_description'],
                            $data['description']
                        ];
                    }
                    if (!$metaTitle) {
                        $metaTitle = $data['meta_title'];
                    }
                    if (!$metaDescription) {
                        $metaDescription = $data['meta_description'];
                    }
                    if (!$description) {
                        $description = $data['description'];
                    }
                }
            }
            $entity
                ->setContent($content)
                ->setMetaTitle($metaTitle)
                ->setMetaDescription($metaDescription)
                ->setDescription($description);
        }
        return $entity;
    }
}
