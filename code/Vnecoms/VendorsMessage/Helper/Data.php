<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Data extends AbstractHelper
{
    const XML_PATH_NEW_MESSAGE_EMAIL_TEMPLATE = 'vendors/vendorsmessage/new_message_notification';
    const XML_PATH_EMAIL_SENDER = 'vendors/vendorsmessage/sender_email_identity';
    
    
    /**
     * @var \Vnecoms\Vendors\Helper\Email
     */
    protected $_emailHelper;
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Vnecoms\VendorsMessage\Model\ResourceModel\Pattern\CollectionFactory
     */
    protected $_pattern;

    /**
     * Constructor
     * 
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Vnecoms\Vendors\Helper\Email $emailHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Vnecoms\Vendors\Helper\Email $emailHelper,
        \Vnecoms\VendorsMessage\Model\ResourceModel\Pattern\CollectionFactory $pattern
    ) {
        parent::__construct($context);
        $this->_emailHelper = $emailHelper;
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->_pattern = $pattern;
    }
    
    
    /**
     * Send new message notification email to receiver
     * 
     * @param \Vnecoms\VendorsMessage\Model\Message\Detail $messageDetail
     */
    public function sendNewReviewNotificationToCustomer(
        \Vnecoms\VendorsMessage\Model\Message\Detail $messageDetail
    ) {
        $messageURL = $this->_urlBuilder->getUrl('customer/message/view',['id' => $messageDetail->getMessageId()]);
        $this->_emailHelper->sendTransactionEmail(
            self::XML_PATH_NEW_MESSAGE_EMAIL_TEMPLATE,
            \Magento\Framework\App\Area::AREA_FRONTEND,
            self::XML_PATH_EMAIL_SENDER,
            $messageDetail->getReceiverEmail(),
            ['message' => $messageDetail, 'message_url' => $messageURL]
        );
    }

    /**
     * @param $message
     * @return array
     */
    public function processPatternWarning($message){
        $patterns = $this->_pattern->create()->addFieldToFilter("action",1)->addFieldToFilter("status",1);
        $warning = ["flag"=>false];
        foreach ($patterns as $pattern){
           // var_dump($message);exit;
            if(preg_match("/".$pattern->getPattern()."/is",$message)){
                $warning["flag"] = true;
                $warning["message"] = $pattern->getMessage();
                break;
            }
        }
        return $warning;
    }

    /**
     * @param $message
     * @return array
     */
    public function processPatternError($message){
        $patterns = $this->_pattern->create()->addFieldToFilter("action",0)->addFieldToFilter("status",1);
        $errors = ["flag"=>false];
        foreach ($patterns as $pattern){
            if(preg_match("/".$pattern->getPattern()."/is",$message)){
                $errors["flag"] = true;
                $errors["message"] = $pattern->getMessage();
                break;
            }
        }
        return $errors;
    }
}
