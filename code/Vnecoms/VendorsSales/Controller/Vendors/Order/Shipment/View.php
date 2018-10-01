<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsSales\Controller\Vendors\Order\Shipment;

use Magento\Framework\Registry;

class View extends \Vnecoms\Vendors\App\AbstractAction
{
    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader
     */
    protected $shipmentLoader;

    /**
     * @var Registry
     */
    protected $registry;
    
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        \Vnecoms\Vendors\App\Action\Context $context,
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
        $this->shipmentLoader = $shipmentLoader;
        $this->registry = $context->getCoreRegsitry();
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }

    /**
     * Shipment information page
     *
     * @return void
     */
    public function execute()
    {
        $this->shipmentLoader->setShipmentId($this->getRequest()->getParam('shipment_id'));
        $shipment = $this->shipmentLoader->load();
        if ($shipment) {
            $vendorOrder = $this->_objectManager->create('Vnecoms\VendorsSales\Model\Order')->load($shipment->getVendorOrderId());


            if ($vendorOrder->getVendorId() != $this->_session->getVendor()->getId()) {
                $resultForward = $this->resultForwardFactory->create();
                $resultForward->forward('noroute');
                return $resultForward;
            }

            $this->registry->register('vendor_order', $vendorOrder);
            
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getLayout()->getBlock('sales_shipment_view')
                ->updateBackButtonUrl($this->getRequest()->getParam('come_from'));
//             $resultPage->setActiveMenu('Vnecoms_VendorsSales::sales_orders');
            $resultPage->getConfig()->getTitle()->prepend(__('Shipments'));
            $resultPage->getConfig()->getTitle()->prepend("#" . $shipment->getIncrementId());
            return $resultPage;
        } else {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('noroute');
            return $resultForward;
        }
    }
}
