<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\Vendors\Controller\Vendors\Index;

class Index extends \Vnecoms\Vendors\Controller\Vendors\Action
{

    /**
     * @return void
     */
    public function execute()
    {
        $key = $this->getRequest()->getModuleName()."_".$this->getRequest()->getActionName();
        if ($key == 'vendors_index') {
            $this->_redirectUrl($this->_backendUrl->getStartupPageUrl());
            return;
        }
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__("Account"));
        $this->_addBreadcrumb(__("Account"), __("Account"));
        $vendor = $this->_session->getVendor();
        $this->_coreRegistry->register('current_vendor', $vendor);
        $this->_view->renderLayout();
    }
}
