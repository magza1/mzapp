<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Controller\Vendors\View;

class Delete extends \Vnecoms\Vendors\Controller\Vendors\Action
{

    /**
     * @return void
     */
    public function execute()
    {
        $message = $this->_objectManager->create('Vnecoms\VendorsMessage\Model\Message');
        $message->load($this->getRequest()->getParam('id'));
        
        if (!$message->getId() || $message->getOwnerId() != $this->_session->getCustomerId()) {
            $this->messageManager->addError(__("The message is not available !"));
            return $this->_redirect('message');
        }
        
        $message->trash();
        
        $this->messageManager->addSuccess(__('The message has been deleted.'));
        return $this->_redirect('message');
    }
}
