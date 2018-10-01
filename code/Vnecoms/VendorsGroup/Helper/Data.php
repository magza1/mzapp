<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsGroup\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Data extends AbstractHelper
{
    const XML_PATH_CAN_ADD_NEW_PRODUCT = 'catalog/can_add_product';
    const XML_PATH_PRODUCT_LIMITATION = 'catalog/product_limit';
    const XML_PATH_SALES_CAN_CANCEL = 'sales/can_cancel';
    const XML_PATH_SALES_CAN_CREATE_INVOICE = 'sales/can_create_invoice';
    const XML_PATH_SALES_CAN_CREATE_SHIPMENT = 'sales/can_create_shipment';
    const XML_PATH_SALES_CAN_CREATE_CREDITMEMO = 'sales/can_create_creditmemo';
    const XML_PATH_SALES_CAN_SUBMIT_ORDER_COMMENTS = 'sales/can_submit_order_comments';
    const XML_PATH_SALES_HIDE_CUSTOMER_EMAIL = 'sales/hide_customer_email';
    const XML_PATH_SALES_HIDE_PAYMENT_INFO = 'sales/hide_payment_info';
    
    /**
     * @var \Vnecoms\VendorsGroup\Model\Config\Reader
     */
    protected $_configReader;
    
    /**
     * @var \Vnecoms\VendorsGroup\Model\ResourceModel\ConfigFactory
     */
    protected $_configResourceFactory;
    
    /**
     * @var \Vnecoms\VendorsGroup\Model\ResourceModel\Config
     */
    protected $_configResource;
    
    /**
     * Constructor
     * 
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Vnecoms\VendorsGroup\Model\Config\Reader $configReader
     * @param \Vnecoms\VendorsGroup\Model\ResourceModel\ConfigFactory $configResourceFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Vnecoms\VendorsGroup\Model\Config\Reader $configReader,
        \Vnecoms\VendorsGroup\Model\ResourceModel\ConfigFactory $configResourceFactory
    ) {
        parent::__construct($context);
        $this->_configReader = $configReader;
        $this->_configResourceFactory = $configResourceFactory;
    }
    
    /**
     * Get Group Config
     * 
     * @return multitype:
     */
    public function getGroupConfig(){
        $config = $this->_configReader->read();
        return $config;
    }
    
    /**
     * Get config by resource id and group ID
     * 
     * @param string $resourceId
     * @param string $groupId
     */
    public function getConfig($resourceId, $groupId){
        if(!$this->_configResource){
            $this->_configResource = $this->_configResourceFactory->create();
        }
        $result = $this->_configResource->getConfig($resourceId, $groupId);
        if($result === false){
            $result = $this->scopeConfig->getValue('vendor_advanced_group/'.$resourceId);
        }
        return $result;
    }
    
    /**
     * Can add new product
     * 
     * @param int $groupId
     * @return boolean
     */
    public function canAddNewProduct($groupId){
        return $this->getConfig(self::XML_PATH_CAN_ADD_NEW_PRODUCT, $groupId);
    }
    
    /**
     * Get product limit
     *
     * @param int $groupId
     * @return boolean
     */
    public function getProductLimit($groupId){
        return $this->getConfig(self::XML_PATH_PRODUCT_LIMITATION, $groupId);
    }
    
    
    /**
     * Can cancel order
     *
     * @param int $groupId
     * @return boolean
     */
    public function canCancelOrder($groupId){
        return $this->getConfig(self::XML_PATH_SALES_CAN_CANCEL, $groupId);
    }
    
    /**
     * Can create invoice
     *
     * @param int $groupId
     * @return boolean
     */
    public function canCreateInvoice($groupId){
        return $this->getConfig(self::XML_PATH_SALES_CAN_CREATE_INVOICE, $groupId);
    }
    
    /**
     * Can create shipment
     *
     * @param int $groupId
     * @return boolean
     */
    public function canCreateShipment($groupId){
        return $this->getConfig(self::XML_PATH_SALES_CAN_CREATE_SHIPMENT, $groupId);
    }
    
    /**
     * Can create credit memo
     *
     * @param int $groupId
     * @return boolean
     */
    public function canCreateCreditMemo($groupId){
        return $this->getConfig(self::XML_PATH_SALES_CAN_CREATE_CREDITMEMO, $groupId);
    }
    
    /**
     * Can Submit Order Comments
     *
     * @param int $groupId
     * @return boolean
     */
    public function canSubmitOrderComment($groupId){
        return $this->getConfig(self::XML_PATH_SALES_CAN_SUBMIT_ORDER_COMMENTS, $groupId);
    }
    
    /**
     * Hide Customer Email
     *
     * @param int $groupId
     * @return boolean
     */
    public function hideCustomerEmail($groupId){
        return $this->getConfig(self::XML_PATH_SALES_HIDE_CUSTOMER_EMAIL, $groupId);
    }
    
    /**
     * Hide Payment Information
     *
     * @param int $groupId
     * @return boolean
     */
    public function hidePaymentInfo($groupId){
        return $this->getConfig(self::XML_PATH_SALES_HIDE_PAYMENT_INFO, $groupId);
    }
}
