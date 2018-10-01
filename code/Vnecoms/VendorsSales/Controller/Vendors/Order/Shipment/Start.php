<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsSales\Controller\Vendors\Order\Shipment;

class Start extends \Vnecoms\Vendors\App\AbstractAction
{

    /**
     * Start create shipment action
     *
     * @return void
     */
    public function execute()
    {
        /**
         * Clear old values for shipment qty's
         */
        $this->_redirect('*/*/new', ['order_id' => $this->getRequest()->getParam('order_id')]);
    }
}
