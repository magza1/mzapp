<?php
namespace Vnecoms\VendorsMessage\Block;

class Message extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    
    /**
     * @var \Vnecoms\Vendors\Model\VendorFactory
     */
    protected $_vendorFactory;
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;


    /**
     * @var \Vnecoms\Vendors\Helper\Data
     */
    protected $_vendorHelper;
    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Vnecoms\Vendors\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Vnecoms\VendorsPage\Helper\Data $pageHelper
     * @param \Vnecoms\Vendors\Helper\Data $vendorHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Vnecoms\Vendors\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Vnecoms\Vendors\Helper\Data $vendorHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_coreRegistry = $registry;
        $this->_vendorFactory = $vendorFactory;
        $this->_customerSession = $customerSession;
        $this->_vendorHelper = $vendorHelper;
    }
    
    
    /**
     * Get vendor object
     *
     * @return \Vnecoms\Vendors\Model\Vendor
     */
    public function getVendor()
    {
        $vendor = $this->_coreRegistry->registry('vendor');
        if (!$vendor && $product = $this->_coreRegistry->registry('product')) {
            if ($vendorId = $product->getVendorId()) {
                $vendor = $this->_vendorFactory->create()->load($vendorId);
            }
        }
        return $vendor;
    }
    
    /**
     * Is logged in customer
     *
     * @return boolean
     */
    public function isLoggedIn()
    {
        return $this->_customerSession->isLoggedIn();
    }
    
    /**
     * Get send message URL
     *
     * @return string
     */
    public function getSendUrl()
    {
        return $this->getUrl('customer/message/send', ['vendor_id' => $this->getVendor()->getId()]);
    }
    
    /**
     * Can send message
     *
     * @return boolean
     */
    public function canSendMessage()
    {
        $sellerCustomerId = $this->getVendor()->getCustomer()->getId();
        return $this->_customerSession->getCustomerId() != $sellerCustomerId;
    }
    
    protected function _toHtml()
    {
        if (!$this->getVendor() || !$this->_vendorHelper->moduleEnabled() ) {
            return '';
        }
        return parent::_toHtml();
    }
}
