<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Block\Message;

/**
 * Class Link
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Link extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    
    /**
     * @var \Vnecoms\VendorsMessage\Model\MessageFactory
     */
    protected $_messageFactory;
    
    /**
     * @var \Vnecoms\VendorsMessage\Model\ResourceModel\Message\Collection
     */
    protected $_unreadMessageCollection;
    
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Vnecoms\VendorsMessage\Model\MessageFactory $messageFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_customerSession = $customerSession;
        $this->_messageFactory = $messageFactory;
    }

    /**
     * Get Unread Message Collection
     *
     * @return \Vnecoms\VendorsMessage\Model\ResourceModel\Message\Collection
     */
    public function getUnreadMessageCollection(){
        if(!$this->_unreadMessageCollection){
            $this->_unreadMessageCollection = $this->_messageFactory->create()->getCollection();
            $this->_unreadMessageCollection->addFieldToFilter('owner_id',$this->_customerSession->getCustomerId())
                ->addFieldToFilter('status',\Vnecoms\VendorsMessage\Model\Message::STATUS_UNDREAD)
                ->addFieldToFilter('is_inbox',1)
                ->addFieldToFilter('is_deleted',0)
                ->setOrder('message_id','DESC')
                ->setPageSize(5);
        }
    
        return $this->_unreadMessageCollection;
    }
    
    
    /**
     * Get Unread Message Count
     *
     * @return int
     */
    public function getUnreadMessageCount(){
        return $this->getUnreadMessageCollection()->getSize();
    }
    
    /**
     * @return string
     */
    public function getMessageUrl()
    {
        return $this->getUrl('customer/message');
    }

    public function toHtml(){
        if(!$this->_customerSession->isLoggedIn()) return '';
        return parent::toHtml();
    }
}
