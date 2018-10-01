<?php
namespace Vnecoms\VendorsMessage\Model\Message;

class Detail extends \Magento\Framework\Model\AbstractModel
{    
    const ENTITY = 'vendor_message_detail';
    
    /**
     * Model event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'vendor_message_detail';
    
    /**
     * Name of the event object
     *
     * @var string
     */
    protected $_eventObject = 'vendor_message_detail';
    
    /**
     * Initialize customer model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Vnecoms\VendorsMessage\Model\ResourceModel\Message\Detail');
    }    
}
