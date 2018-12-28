<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\ResourceModel;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class BrandAttributeOption
 * @package Aheadworks\ShopByBrand\Model\ResourceModel
 */
class BrandAttributeOption extends AbstractDb
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
    protected function _construct()
    {
        $this->_init('aw_sbb_brand', 'brand_id');
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
     * Get IDs of options that used as brands
     *
     * @return array
     */
    public function getUsedOptionIds()
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->_resources->getTableName('aw_sbb_brand'), 'option_id');
        return $connection->fetchCol($select);
    }
}
