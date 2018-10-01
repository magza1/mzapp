<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsNotification\Controller\Vendors\Index;

class Index extends \Vnecoms\Vendors\Controller\Vendors\Action
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $title = $this->_view->getPage()->getConfig()->getTitle();
        $title->prepend(__("Notifications"));
        
        $this->_addBreadcrumb(__("Notifications"), __("Notifications"));
        $this->_view->renderLayout();
    }
}
