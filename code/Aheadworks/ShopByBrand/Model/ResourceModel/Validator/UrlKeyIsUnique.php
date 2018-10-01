<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\ResourceModel\Validator;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Class UrlKeyIsUnique
 * @package Aheadworks\ShopByBrand\Model\ResourceModel\Validator
 */
class UrlKeyIsUnique
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Checks whether the URL-Key is unique
     *
     * @param BrandInterface $brand
     * @return bool
     */
    public function validate($brand)
    {
        $connection = $this->resourceConnection->getConnectionByName(
            $this->metadataPool->getMetadata(BrandInterface::class)->getEntityConnectionName()
        );
        $bind = ['url_key' => $brand->getUrlKey()];
        $select = $connection->select()
            ->from($this->resourceConnection->getTableName('aw_sbb_brand'))
            ->where('url_key = :url_key');
        if ($brand->getBrandId()) {
            $select->where('brand_id <> :brand_id');
            $bind['brand_id'] = $brand->getBrandId();
        }
        if ($connection->fetchRow($select, $bind)) {
            return false;
        }
        return true;
    }
}
