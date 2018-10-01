<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Controller\Message;

use Magento\Framework\Exception\NotFoundException;
use Vnecoms\VendorsMessage\Model\Message;

class Send extends \Magento\Framework\App\Action\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Customer session
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    
    /**
     * @var \Vnecoms\VendorsMessage\Helper\Data
     */
    protected $_messageHelper;
    
    /**
     * Constructor
     * 
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        \Vnecoms\VendorsMessage\Helper\Data $messageHelper
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_customerSession = $customerSession;
        $this->_messageHelper = $messageHelper;
    }
    
    /**
     * Display customer wishlist
     *
     * @return \Magento\Framework\View\Result\Page
     * @throws NotFoundException
     */
    public function execute()
    {
        
        
        /*If not logged in*/
        
        $vendor = $this->_objectManager->create('Vnecoms\Vendors\Model\Vendor');
        $vendor->load($this->getRequest()->getParam('vendor_id'));
        
        if(!$vendor->getId() || !$this->_customerSession->isLoggedIn()){
            /*The request is not valid*/
            $this->messageManager->addError(__("The request is not valid."));
        }else{
            $receiver = $vendor->getCustomer();
            $sender = $this->_customerSession->getCustomer();
            
            $message = $this->_objectManager->create('Vnecoms\VendorsMessage\Model\Message');
            $messageDetail = $this->_objectManager->create('Vnecoms\VendorsMessage\Model\Message\Detail');
            
            $identifier = md5(md5($sender->getId().$receiver->getId()).time());
            
            $senderMsgData = [
                'identifier' => $identifier,
                'owner_id' => $sender->getId(),
                'status' => Message::STATUS_SENT,
                'is_inbox' => 0,
                'is_outbox' => 1,
                'is_deleted' => 0,
            ];
            $receiverMsgData = [
                'identifier' => $identifier,
                'owner_id' => $receiver->getId(),
                'status' => Message::STATUS_UNDREAD,
                'is_inbox' => 1,
                'is_outbox' => 0,
                'is_deleted' => 0,
            ];
            $msgDetailData =[
                'sender_id' => $sender->getId(),
                'sender_email' => $sender->getEmail(),
                'sender_name' => $sender->getName(),
                'receiver_id' => $receiver->getId(),
                'receiver_email' => $receiver->getEmail(),
                'receiver_name' => $receiver->getName(),
                'subject' => $this->getRequest()->getParam('subject'),
                'content' => $this->getRequest()->getParam('content'),
                'is_read' => 0,
            ];
            
            /*Save the message to sender outbox*/
            $message->setData($senderMsgData)->save();
            $messageDetail->setData($msgDetailData)->setMessageId($message->getId())->save();
            
            /*Save the message to receiver inbox*/
            $message->setData($receiverMsgData)->save();
            $messageDetail->setData($msgDetailData)->setMessageId($message->getId())->save();
            
            /*Send notification email to receiver*/
            $this->_messageHelper->sendNewReviewNotificationToCustomer($messageDetail);
            
            $this->messageManager->addSuccess(__("Your message is sent successfully."));
        }        
    }
}
