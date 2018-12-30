<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Vnecoms\VendorsConfig\Model\ResourceModel;

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
        $this->_init('ves_vendor_config', 'config_id');
    }
    
    /**
     * Get vendor configuration data
     * 
     * @param string $path
     * @param int $vendorId
     */
    public function getConfigData($path, $vendorId){
        $connection = $this->getConnection();
        $bind    = [
            'path' => $path,
            'vendor_id' => $vendorId
        ];
        $select  = $connection->select()
            ->from($this->getMainTable(), array('value'))
            ->where('path = :path')
            ->where('vendor_id = :vendor_id');
        $result = $connection->fetchOne($select, $bind);
        return $result;
    }
}
