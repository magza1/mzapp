<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsProduct\Controller\Vendors\Product;

class Index extends \Vnecoms\Vendors\Controller\Vendors\Action
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $title = $this->_view->getPage()->getConfig()->getTitle();
        $title->prepend(__("Catalog"));
        $title->prepend(__("Manage Products"));
        
        $this->_addBreadcrumb(__("Catalog"), __("Catalog"))->_addBreadcrumb(__("Manage Products"), __("Manage Products"));
        $this->_view->renderLayout();
    }
}
