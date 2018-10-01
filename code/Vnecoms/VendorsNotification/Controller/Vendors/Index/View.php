<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsNotification\Controller\Vendors\Index;

class View extends \Vnecoms\Vendors\Controller\Vendors\Action
{

    /**
     * @return void
     */
    public function execute()
    {
        $notification = $this->_objectManager->create('Vnecoms\VendorsNotification\Model\Notification');
        $notification->load($this->getRequest()->getParam('id'));
        
        if (!$notification->getId()) {
            $this->messageManager->addError(__("The notification is not exist !"));
            return $this->_redirect('dashboard');
        }
        /* Mark the notification as read */
        if (!$notification->getData('is_read')) {
            $notification->setData('is_read', 1)->save();
        }
        /* Redirect to the destimation URL*/
        $this->_redirectUrl($notification->getNotificationType()->getUrl());
    }
}
