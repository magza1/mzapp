<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Controller\Message;

use Vnecoms\VendorsMessage\Model\Message;
use Magento\Framework\App\Action\Context;

class Reply extends \Magento\Customer\Controller\AbstractAccount
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
     * @var \Vnecoms\VendorsMessage\Helper\Data
     */
    protected $_messageHelper;
    
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $coreRegistry,
        \Vnecoms\VendorsMessage\Helper\Data $messageHelper
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_customerSession = $customerSession;
        $this->_messageHelper = $messageHelper;
        parent::__construct($context);
    }
    
    /**
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
       $message = $this->_objectManager->create('Vnecoms\VendorsMessage\Model\Message');
        $message->load($this->getRequest()->getParam('id'));
        try{
            if(!$message->getId() || $message->getOwnerId() != $this->_customerSession->getCustomerId()){
                throw new \Exception(__("The message is not available."));
            }
            
            $sender = $this->_customerSession->getCustomer();
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

            $errors = [];
            $warnings = [];
            $transport = new \Magento\Framework\DataObject(
                [
                    'detail_data'=>$msgDetailData ,
                    'errors'=>$errors,
                    'warnings' => $warnings
                ]
            );
            /*Save the message to sender outbox*/
            $this->_eventManager->dispatch(
                'messsage_prepare_save',
                [
                    'transport'=>$transport ,
                ]
            );
            $errors = $transport->getErrors();
            $warnings = $transport->getWarnings();
            if($errors){
                throw new \Exception(implode("<br />", $errors));
            }

            $result = [];

            $messageDetail = $this->_objectManager->create('Vnecoms\VendorsMessage\Model\Message\Detail');
            
            $relationMessage = $message->getRelationMessage();
            
            /*Save the detail message to sender outbox*/           
            $messageDetail->setData($msgDetailData)->setData('is_read',1)->setMessageId($message->getId())->save();

            if($warnings){
                $result["msg"] = implode("<br />", $warnings);
                $warningData = [
                    'message_id'  => $message->getId(),
                    'detail_message_id' =>   $messageDetail->getId()
                ];
                $warning = $this->_objectManager->create('Vnecoms\VendorsMessage\Model\Warning');
                $warning->setData($warningData)->save();
            }
            
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
            
            $block = $this->_view->getLayout()->createBlock('Vnecoms\VendorsMessage\Block\Customer\Account\Message\View')->setTemplate('customer/account/message/view/list.phtml');
            
            $result[ 'error'] = false;
            $result[ 'message_list'] = $block->toHtml();

        }catch (\Exception $e){
            $result = [
                'error' => true,
                'msg' => $e->getMessage(),
            ];
        }
        
        $this->getResponse()->setBody(json_encode($result));
    }
}
