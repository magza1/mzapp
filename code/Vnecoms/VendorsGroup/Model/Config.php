<?php
namespace Vnecoms\VendorsGroup\Model;

class Config extends \Magento\Framework\Model\AbstractModel
{

    const ENTITY = 'group_config';
    
    /**
     * Model event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'vendor_group_config';
    
    /**
     * Name of the event object
     *
     * @var string
     */
    protected $_eventObject = 'vendor_group_config';
    
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Vnecoms\VendorsGroup\Model\ResourceModel\Config');
    }
}
