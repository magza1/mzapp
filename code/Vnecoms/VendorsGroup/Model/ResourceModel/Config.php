<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Vnecoms\VendorsGroup\Model\ResourceModel;

/**
 * Cms page mysql resource
 */
class Config extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ves_vendor_group_config', 'config_id');
    }
    
    /**
     * Get Config
     * 
     * @param string $resourceId
     * @param int $groupId
     * @return string
     */
    public function getConfig($resourceId, $groupId){
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), 'value')
            ->where('resource_id = :resource_id')
            ->where('group_id = :group_id');
        $bind = array('resource_id'=>$resourceId, 'group_id'=>$groupId);
        $result = $adapter->fetchOne($select, $bind);
        return $result;
    }
}
