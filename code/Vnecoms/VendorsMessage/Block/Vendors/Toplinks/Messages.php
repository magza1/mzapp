<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Vnecoms\VendorsMessage\Block\Vendors\Toplinks;

/**
 * Vendor Notifications block
 */
class Messages extends \Vnecoms\Vendors\Block\Vendors\AbstractBlock
{
    /**
     * @var \Vnecoms\VendorsMessage\Model\MessageFactory
     */
    protected $_messageFactory;
    
    /**
     * @var \Vnecoms\Vendors\Model\Session
     */
    protected $_vendorSession;
    
    /**
     * @var \Vnecoms\VendorsMessage\Model\ResourceModel\Message\Collection
     */
    protected $_unreadMessageCollection;
    
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Vnecoms\Vendors\Model\UrlInterface $url,
        \Vnecoms\VendorsMessage\Model\MessageFactory $messageFactory,
        \Vnecoms\Vendors\Model\Session $vendorSession,
        array $data = []
    ) {
        parent::__construct($context, $url, $data);
        $this->_messageFactory = $messageFactory;
        $this->_vendorSession = $vendorSession;
    }
    
    /**
     * Get Pending Credit URL
     * 
     * @return string
     */
    public function getPendingCreditUrl(){
        return $this->getUrl('credit/pending');
    }
    
    /**
     * Get Unread Message Collection
     * 
     * @return \Vnecoms\VendorsMessage\Model\ResourceModel\Message\Collection
     */
    public function getUnreadMessageCollection(){
        if(!$this->_unreadMessageCollection){
            $this->_unreadMessageCollection = $this->_messageFactory->create()->getCollection();
            $this->_unreadMessageCollection->addFieldToFilter('owner_id',$this->_vendorSession->getCustomerId())
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
     * Format message time
     * 
     * @param string $dateTime
     */
    public function getMessageTime($dateTime){
        $messageTimeStamp = strtotime($dateTime);
        $timeStamp = time();
        
        $differentTime = $timeStamp - $messageTimeStamp;
        $minutes = round($differentTime / 60);
        if($minutes == 0) return __("Now");
        
        elseif($minutes < 60){
            return __("%1 minutes", $minutes);
        }
        
        $hours = round($minutes / 60);
        if($hours < 24) return __("Today");
        
        $days = round($hours / 24);
        if($days == 1) return __("Yesterday");
        if($days < 7) return __("%1 days", $days);
        
        if($days < 365) return $this->formatDate($dateTime, \IntlDateFormatter::SHORT);
        
        return $this->formatDate($dateTime, \IntlDateFormatter::SHORT);
    }
    
    /**
     * Get Message URL
     * 
     * @return string
     */
    public function getMessageUrl(){
        return $this->getUrl('message');
    }
    
    /**
     * Get View message URL
     * 
     * @param \Vnecoms\VendorsMessage\Model\Message $message
     * @return string
     */
    public function getViewMessageUrl(
        \Vnecoms\VendorsMessage\Model\Message $message
    ) {
        return $this->getUrl('message/view/index',['id' => $message->getId()]);
    }
}
