<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsDashboard\Controller\Vendors\Index;

class Index extends \Vnecoms\Vendors\Controller\Vendors\Action
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__("Dashboard"));
        $this->_addBreadcrumb(__("Dashboard"), __("Dashboard"));
        $this->_view->renderLayout();
    }
}
