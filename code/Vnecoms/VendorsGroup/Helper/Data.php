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
    const XML_PATH_CAN_ADD_NEW_PRODUCT              = 'catalog/can_add_product';
    const XML_PATH_PRODUCT_LIMITATION               = 'catalog/product_limit';
    const XML_PATH_SALES_CAN_CANCEL                 = 'sales/can_cancel';
    const XML_PATH_SALES_CAN_CREATE_INVOICE         = 'sales/can_create_invoice';
    const XML_PATH_SALES_CAN_CREATE_SHIPMENT        = 'sales/can_create_shipment';
    const XML_PATH_SALES_CAN_CREATE_CREDITMEMO      = 'sales/can_create_creditmemo';
    const XML_PATH_SALES_CAN_SUBMIT_ORDER_COMMENTS  = 'sales/can_submit_order_comments';
    const XML_PATH_SALES_HIDE_CUSTOMER_EMAIL        = 'sales/hide_customer_email';
    const XML_PATH_SALES_HIDE_PAYMENT_INFO          = 'sales/hide_payment_info';
    const XML_PATH_DOMAIN_CAN_USE_SUBDOMAIN         = 'domain/can_use_subdomain';
    const XML_PATH_DOMAIN_CAN_EDIT_SUBDOMAIN        = 'domain/can_edit_subdomain';
    const XML_PATH_DOMAIN_CAN_USE_DOMAIN            = 'domain/can_use_domain';
    const XML_PATH_MESSAGE_CAN_USE_MESSAGE          = 'message/can_use_message';
    const XML_PATH_CMS_CAN_USE_CMS                  = 'vendorscms/can_use_cms';
    const XML_PATH_REPORT_CAN_USE_REPORT            = 'vendors_report/can_use_report';
    const XML_PATH_IMPORT_EXPORT_CAN_USE_IMPORT_EXPORT                = 'product_import_export/can_use_import_export';
    const XML_PATH_STORE_LOCATOR_CAN_USE_STORE_LOCATOR                = 'store_locator/can_use_store_locator';
    const XML_PATH_VENDOR_SMS_CAN_USE_VENDOR_SMS                      = 'vendors_sms/can_use_sms';
    const XML_PATH_VENDOR_CATEGORY_CAN_USE_VENDOR_CATEGORY            = 'vendors_category/can_use_category';
    const XML_PATH_SELECT_AND_SELL_CAN_USE_SELECT_AND_SELL            = 'select_and_sell/can_use_select_and_sell';
    const XML_PATH_VENDOR_VACATION_CAN_USE_VENDOR_VACATION            = 'vendors_vacation/can_use_vacation';
    const XML_PATH_VENDOR_SHIPPING_METHOD_CAN_USE_VENDOR_SHIPPING_METHOD            = 'vendors_shipping_method/can_use_shipping_method';

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
    public function getGroupConfig()
    {
        $config = $this->_configReader->read();
        return $config;
    }
    
    /**
     * Get config by resource id and group ID
     *
     * @param string $resourceId
     * @param string $groupId
     */
    public function getConfig($resourceId, $groupId)
    {
        if (!$this->_configResource) {
            $this->_configResource = $this->_configResourceFactory->create();
        }
        $result = $this->_configResource->getConfig($resourceId, $groupId);
        if ($result === false) {
            $result = $this->scopeConfig->getValue('vendor_advanced_group/'.$resourceId);
        }
        return $result;
    }
    
    /**
     *
     *
     * @param int $groupId
     * @return boolean
     */
    public function canAddNewProduct($groupId)
    {
        return $this->getConfig(self::XML_PATH_CAN_ADD_NEW_PRODUCT, $groupId);
    }

    /**
     *
     *
     * @param int $groupId
     * @return boolean
     */
    public function canUseVendorSMS($groupId)
    {
        return $this->getConfig(self::XML_PATH_VENDOR_SMS_CAN_USE_VENDOR_SMS, $groupId);
    }

    /**
     *
     *
     * @param int $groupId
     * @return boolean
     */
    public function canUseCategory($groupId)
    {
        return $this->getConfig(self::XML_PATH_VENDOR_CATEGORY_CAN_USE_VENDOR_CATEGORY, $groupId);
    }

    /**
     *
     *
     * @param int $groupId
     * @return boolean
     */
    public function canUseShippingMethod($groupId)
    {
        return $this->getConfig(self::XML_PATH_VENDOR_SHIPPING_METHOD_CAN_USE_VENDOR_SHIPPING_METHOD, $groupId);
    }

    /**
     *
     *
     * @param int $groupId
     * @return boolean
     */
    public function canUseVacation($groupId)
    {
        return $this->getConfig(self::XML_PATH_VENDOR_VACATION_CAN_USE_VENDOR_VACATION, $groupId);
    }

    /**
     * Can add new product
     *
     * @param int $groupId
     * @return boolean
     */
    public function canUseStoreLocator($groupId)
    {
        return $this->getConfig(self::XML_PATH_STORE_LOCATOR_CAN_USE_STORE_LOCATOR, $groupId);
    }

    /**
     *
     *
     * @param int $groupId
     * @return boolean
     */
    public function canUseProductImportExport($groupId)
    {
        return $this->getConfig(self::XML_PATH_IMPORT_EXPORT_CAN_USE_IMPORT_EXPORT, $groupId);
    }

    /**
     *
     *
     * @param int $groupId
     * @return boolean
     */
    public function canUseReport($groupId)
    {
        return $this->getConfig(self::XML_PATH_REPORT_CAN_USE_REPORT, $groupId);
    }

    /**
     *
     *
     * @param int $groupId
     * @return boolean
     */
    public function canUseCMS($groupId)
    {
        return $this->getConfig(self::XML_PATH_CMS_CAN_USE_CMS, $groupId);
    }

    /**
     *
     *
     * @param int $groupId
     * @return boolean
     */
    public function canUseMessage($groupId)
    {
        return $this->getConfig(self::XML_PATH_MESSAGE_CAN_USE_MESSAGE, $groupId);
    }

    /**
     *
     *
     * @param int $groupId
     * @return boolean
     */
    public function canUseSelectAndSell($groupId)
    {
        return $this->getConfig(self::XML_PATH_SELECT_AND_SELL_CAN_USE_SELECT_AND_SELL, $groupId);
    }

    /**
     * Get product limit
     *
     * @param int $groupId
     * @return boolean
     */
    public function getProductLimit($groupId)
    {
        return $this->getConfig(self::XML_PATH_PRODUCT_LIMITATION, $groupId);
    }
    
    
    /**
     * Can cancel order
     *
     * @param int $groupId
     * @return boolean
     */
    public function canCancelOrder($groupId)
    {
        return $this->getConfig(self::XML_PATH_SALES_CAN_CANCEL, $groupId);
    }
    
    /**
     * Can create invoice
     *
     * @param int $groupId
     * @return boolean
     */
    public function canCreateInvoice($groupId)
    {
        return $this->getConfig(self::XML_PATH_SALES_CAN_CREATE_INVOICE, $groupId);
    }
    
    /**
     * Can create shipment
     *
     * @param int $groupId
     * @return boolean
     */
    public function canCreateShipment($groupId)
    {
        return $this->getConfig(self::XML_PATH_SALES_CAN_CREATE_SHIPMENT, $groupId);
    }
    
    /**
     * Can create credit memo
     *
     * @param int $groupId
     * @return boolean
     */
    public function canCreateCreditMemo($groupId)
    {
        return $this->getConfig(self::XML_PATH_SALES_CAN_CREATE_CREDITMEMO, $groupId);
    }
    
    /**
     * Can Submit Order Comments
     *
     * @param int $groupId
     * @return boolean
     */
    public function canSubmitOrderComment($groupId)
    {
        return $this->getConfig(self::XML_PATH_SALES_CAN_SUBMIT_ORDER_COMMENTS, $groupId);
    }
    
    /**
     * Hide Customer Email
     *
     * @param int $groupId
     * @return boolean
     */
    public function hideCustomerEmail($groupId)
    {
        return $this->getConfig(self::XML_PATH_SALES_HIDE_CUSTOMER_EMAIL, $groupId);
    }
    
    /**
     * Hide Payment Information
     *
     * @param int $groupId
     * @return boolean
     */
    public function hidePaymentInfo($groupId)
    {
        return $this->getConfig(self::XML_PATH_SALES_HIDE_PAYMENT_INFO, $groupId);
    }
    
    /**
     * Can Use subdomain
     *
     * @param int $groupId
     * @return boolean
     */
    public function canUseSubdomain($groupId){
        return (bool)$this->getConfig(self::XML_PATH_DOMAIN_CAN_USE_SUBDOMAIN, $groupId);
    }
    
    /**
     * Can edit subdomain
     * 
     * @param int $groupId
     * @return boolean
     */
    public function canEditSubdomain($groupId){
        return (bool)$this->getConfig(self::XML_PATH_DOMAIN_CAN_EDIT_SUBDOMAIN, $groupId);
    }
    
    /**
     * Can use domain
     *
     * @param int $groupId
     * @return boolean
     */
    public function canUseDomain($groupId){
        return (bool)$this->getConfig(self::XML_PATH_DOMAIN_CAN_USE_DOMAIN, $groupId);
    }
}
