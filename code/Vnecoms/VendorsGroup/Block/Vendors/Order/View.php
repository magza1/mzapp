<?php
/**
 * Catalog price rules
 *
 * @author      Vnecoms Team <core@vnecoms.com>
 */
namespace Vnecoms\VendorsGroup\Block\Vendors\Order;

class View extends \Magento\Framework\View\Element\Template
{    
    
    /**
     * @var \Vnecoms\VendorsGroup\Helper\Data
     */
    protected $_groupHelper;
    
    /**
     * @var \Vnecoms\Vendors\Model\Session
     */
    protected $_vendorSession;
    
    /**
     * Constructor
     * 
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Vnecoms\VendorsGroup\Helper\Data $groupHelper
     * @param \Vnecoms\Vendors\Model\Session $vendorSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Vnecoms\VendorsGroup\Helper\Data $groupHelper,
        \Vnecoms\Vendors\Model\Session $vendorSession,
        array $data = []
    ) {
        $this->_groupHelper = $groupHelper;
        $this->_vendorSession = $vendorSession;
        
        return parent::__construct($context, $data);
    }
    
    /**
     * Prepare button and grid
     *
     * @return \Magento\Catalog\Block\Adminhtml\Product
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $groupId = $this->_vendorSession->getVendor()->getGroupId();
        if(!$this->_groupHelper->canCancelOrder($groupId)){
            $this->getParentBlock()->removeButton('order_cancel');
        }
        
        if(!$this->_groupHelper->canCreateInvoice($groupId)){
            $this->getParentBlock()->removeButton('order_invoice');
        }
        
        if(!$this->_groupHelper->canCreateShipment($groupId)){
            $this->getParentBlock()->removeButton('order_ship');
        }
        
        if(!$this->_groupHelper->canCreateCreditMemo($groupId)){
            $this->getParentBlock()->removeButton('order_creditmemo');
        }
        
        if(!$this->_groupHelper->canSubmitOrderComment($groupId)){
            $this->getLayout()->getBlock('order_tab_info')->setData('hide_comment', true);
        }
        
        if($this->_groupHelper->hidePaymentInfo($groupId)){
            $this->getLayout()->getBlock('order_tab_info')->setData('hide_payment_info', true);
        }
        
        if($this->_groupHelper->hideCustomerEmail($groupId)){
            $this->getLayout()->getBlock('order_info')->setData('hide_email', true);
        }
        
        return $this;
    }
    
}