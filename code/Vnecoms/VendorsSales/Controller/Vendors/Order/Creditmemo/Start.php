<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsSales\Controller\Vendors\Order\Creditmemo;

class Start extends \Vnecoms\Vendors\App\AbstractAction
{
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Vnecoms_VendorsSales::sales_creditmemo');
    }

    /**
     * Start create creditmemo action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /**
         * Clear old values for creditmemo qty's
         */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/new', ['_current' => true]);
        return $resultRedirect;
    }
}
