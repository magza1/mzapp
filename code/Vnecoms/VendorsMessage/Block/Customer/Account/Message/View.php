<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Block\Customer\Account\Message;

/**
 * Shopping cart item render block for configurable products.
 */
class View extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Vnecoms\VendorsMessage\Model\MessageFactory $messageFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_coreRegistry = $coreRegistry;
        
        parent::__construct($context, $data);
    }
   
    /**
     * Get Current Message
     *
     * @return \Vnecoms\VendorsMessage\Model\Message
     */
    public function getMessage()
    {
        return $this->_coreRegistry->registry('message');
    }
    
    /**
     * Get Back URL
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl("customer/message");
    }
    
    /**
     * Get Back URL
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl("customer/message/delete", ['id' => $this->getMessage()->getId()]);
    }
    
    /**
     * Get Back URL
     *
     * @return string
     */
    public function getSendUrl()
    {
        return $this->getUrl("customer/message/reply", ['id' => $this->getMessage()->getId()]);
    }
}
