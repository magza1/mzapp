<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsSales\Controller\Vendors\Order;

class Cancel extends \Vnecoms\VendorsSales\Controller\Vendors\Order
{
    /**
     * Cancel order
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $order = $this->_initOrder();
        if ($order) {
            $vendorOrder = $this->_coreRegistry->registry('vendor_order');
            try {
                $vendorOrder->cancel();
                $this->messageManager->addSuccess(__('You canceled the order.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('You have not canceled the item.'));
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            }
            return $resultRedirect->setPath('sales/order/view', ['order_id' => $vendorOrder->getId()]);
        }
        return $resultRedirect->setPath('sales/*/');
    }
}
