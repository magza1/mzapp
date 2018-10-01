<?php
/**
 * Catalog price rules
 *
 * @author      Vnecoms Team <core@vnecoms.com>
 */
namespace Vnecoms\VendorsGroup\Block\Vendors;

class Product extends \Magento\Framework\View\Element\Template
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
        if($this->_groupHelper->canAddNewProduct($groupId)) return;
        
        $this->getParentBlock()->removeButton('add_new');
        return $this;
    }
    
}