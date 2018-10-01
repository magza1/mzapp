<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Controller\Vendors\View;

use Vnecoms\VendorsMessage\Model\Message;

class Reply extends \Vnecoms\Vendors\Controller\Vendors\Action
{
    /**
     * @var \Vnecoms\VendorsMessage\Helper\Data
     */
    protected $_messageHelper;
    
    /**
     * Constructor
     * 
     * @param \Vnecoms\Vendors\App\Action\Context $context
     * @param \Vnecoms\VendorsMessage\Helper\Data $messageHelper
     */
    public function __construct(
        \Vnecoms\Vendors\App\Action\Context $context,
        \Vnecoms\VendorsMessage\Helper\Data $messageHelper
    ) {
        parent::__construct($context);
        $this->_messageHelper = $messageHelper;
    }
    
    
    /**
     * @return void
     */
    public function execute()
    {
        $message = $this->_objectManager->create('Vnecoms\VendorsMessage\Model\Message');
        $message->load($this->getRequest()->getParam('id'));
        try{
            if(!$message->getId() || $message->getOwnerId() != $this->_session->getCustomerId()){
                throw new \Exception(__("The message is not available."));
            }
            
            $sender = $this->_session->getCustomer();
            $firstMessageDetail = $message->getFirstMessageDetail();
            /*The receiver id is the id that different with the owner id*/
            $receiverId = $sender->getId() != $firstMessageDetail->getReceiverId()?$firstMessageDetail->getReceiverId():$firstMessageDetail->getSenderId();
            $receiver = $this->_objectManager->create('Magento\Customer\Model\Customer');
            $receiver->load($receiverId);
                
            $msgDetailData =[
                'sender_id' => $sender->getId(),
                'sender_email' => $sender->getEmail(),
                'sender_name' => $sender->getName(),
                'receiver_id' => $receiver->getId(),
                'receiver_email' => $receiver->getEmail(),
                'receiver_name' => $receiver->getName(),
                'subject' => __("Re: %1", $firstMessageDetail->getSubject()),
                'content' => $this->getRequest()->getParam('content'),
                'is_read' => 0,
            ];
            $messageDetail = $this->_objectManager->create('Vnecoms\VendorsMessage\Model\Message\Detail');
            
            $relationMessage = $message->getRelationMessage();
            
            /*Save the detail message to sender outbox*/
            $messageDetail->setData($msgDetailData)->setData('is_read',1)->setMessageId($message->getId())->save();
            
            /*save the detail message to receiver inbox*/
            $messageDetail->setData($msgDetailData)->setMessageId($relationMessage->getId())->save();
            
            /*Send notification email to receiver*/
            $this->_messageHelper->sendNewReviewNotificationToCustomer($messageDetail);
            
            /*No matter what message type is just set the is_in_outbox to true*/
            $message->setIsOutbox(1)
                ->setIsDeleted(0)
                ->save();
            
            /*No matter what message type is just set the is_in_inbox to true*/
            $relationMessage->setIsInbox(1)
                ->setIsDeleted(0)
                ->setStatus(Message::STATUS_UNDREAD)
                ->save();
            
            $this->_coreRegistry->register('current_message', $message);
            $this->_coreRegistry->register('message', $message);
            
            $block = $this->_view->getLayout()->createBlock('Vnecoms\VendorsMessage\Block\Vendors\Messages\View')->setTemplate('view/list.phtml');
            
            $result = [
                'error' => false,
                'message_list' => $block->toHtml()
            ];
        }catch (\Exception $e){
            $result = [
                'error' => true,
                'msg' => $e->getMessage(),
            ];
        }
        
        $this->getResponse()->setBody(json_encode($result));
    }
}
