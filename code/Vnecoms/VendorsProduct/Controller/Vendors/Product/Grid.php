<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsProduct\Controller\Vendors\Product;

class Grid extends \Vnecoms\Vendors\Controller\Vendors\Action
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        return $this->getResponse()->setBody($this->_view->getLayout()->getBlock('vendor.products.grid')->toHtml());
    }
}
