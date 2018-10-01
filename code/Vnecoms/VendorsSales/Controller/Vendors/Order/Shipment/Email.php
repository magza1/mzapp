<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsSales\Controller\Vendors\Order\Shipment;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Email
 *
 * @package Magento\Shipping\Controller\Adminhtml\Order\Shipment
 */
class Email extends \Vnecoms\Vendors\App\AbstractAction
{
    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader
     */
    protected $shipmentLoader;

    /**
     * @param \Vnecoms\Vendors\App\Action\Context $context,
     * @param \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader
     */
    public function __construct(
        \Vnecoms\Vendors\App\Action\Context $context,
        \Vnecoms\VendorsSales\Controller\Vendors\Order\ShipmentLoader $shipmentLoader
    ) {
        $this->shipmentLoader = $shipmentLoader;
        parent::__construct($context);
    }

    /**
     * Check if email sending is allowed for the current user
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Vnecoms_VendorsSales::shipment');
    }

    /**
     * Send email with shipment data to customer
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $vendorOrder = $this->_objectManager->create('Vnecoms\VendorsSales\Model\Order')->load($this->getRequest()->getParam('order_id'));
            $this->shipmentLoader->setOrderId($vendorOrder->getOrderId());
            $this->shipmentLoader->setShipmentId($this->getRequest()->getParam('shipment_id'));
            $this->shipmentLoader->setShipment($this->getRequest()->getParam('shipment'));
            $this->shipmentLoader->setTracking($this->getRequest()->getParam('tracking'));
            $this->shipmentLoader->setVendorOrder($vendorOrder);
            $shipment = $this->shipmentLoader->load();
            if ($shipment) {
                $this->_objectManager->create('Magento\Shipping\Model\ShipmentNotifier')
                    ->notify($shipment);
                $shipment->save();
                $this->messageManager->addSuccess(__('You sent the shipment.'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Cannot send shipment information.'));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/order_shipment/view', ['shipment_id' => $this->getRequest()->getParam('shipment_id')]);
    }
}
