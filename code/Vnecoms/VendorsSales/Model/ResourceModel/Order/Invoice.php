<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Vnecoms\VendorsSales\Model\ResourceModel\Order;

/**
 * Cms page mysql resource
 */
class Invoice extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ves_vendor_sales_invoice', 'entity_id');
    }
    
    /**
     * Update vendor order id for order items.
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_afterSave($object);

        if($object->getItems() && is_array($object->getItems()) && sizeof($object->getItems()) && $object->getId() ){
            $itemIds = [];
            foreach($object->getItems() as $invoiceItem){
                $itemIds[] = $invoiceItem->getId();
            }
            if(!sizeof($itemIds)) return $this;
            
            $adapter   = $this->getConnection();
            $sql = "UPDATE ".$this->getTable('sales_invoice_item').' SET vendor_invoice_id="'.$object->getId().'" WHERE entity_id IN('.implode(",", $itemIds).')';
            return $adapter->query($sql);
        }
        
        
        return $this;
    }
    
    /**
     * Check if the vendor invoice is created by base invoice id.
     * @param int $invoiceId
     * @return boolean
     */
    public function isCreatedVendorInvoice($invoiceId){
        $table = $this->getTable('ves_vendor_sales_invoice');
        $readCollection = $this->getConnection();
        $sql = "SELECT count(entity_id) as invoice_num FROM $table WHERE invoice_id=\"{$invoiceId}\";";
        $count = $readCollection->fetchOne($sql);
        return $count > 0;
    }
}
