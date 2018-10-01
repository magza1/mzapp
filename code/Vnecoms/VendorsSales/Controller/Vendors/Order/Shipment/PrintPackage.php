<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsSales\Controller\Vendors\Order\Shipment;

use Vnecoms\Vendors\App\AbstractAction;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class PrintPackage extends \Vnecoms\Vendors\App\AbstractAction
{

    /**
     * @var \Vnecoms\VendorsSales\Controller\Vendors\Order\ShipmentLoader
     */
    protected $shipmentLoader;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @param Action\Context $context
     * @param \Vnecoms\VendorsSales\Controller\Vendors\Order\ShipmentLoader $shipmentLoader
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        \Vnecoms\Vendors\App\Action\Context  $context,
        \Vnecoms\VendorsSales\Controller\Vendors\Order\ShipmentLoader $shipmentLoader,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->shipmentLoader = $shipmentLoader;
        $this->_fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * Create pdf document with information about packages
     *
     * @return ResponseInterface|void
     */
    public function execute()
    {
        $vendorOrder = $this->_objectManager->create('Vnecoms\VendorsSales\Model\Order')->load($this->getRequest()->getParam('order_id'));
        $this->shipmentLoader->setOrderId($vendorOrder->getOrderId());
        $this->shipmentLoader->setShipmentId($this->getRequest()->getParam('shipment_id'));
        $this->shipmentLoader->setShipment($this->getRequest()->getParam('shipment'));
        $this->shipmentLoader->setTracking($this->getRequest()->getParam('tracking'));
        $this->shipmentLoader->setVendorOrder($vendorOrder);
        $shipment = $this->shipmentLoader->load();

        if ($shipment) {
            /** @var \Zend_Pdf $pdf */
            $pdf = $this->_objectManager->create('Magento\Shipping\Model\Order\Pdf\Packaging')->getPdf($shipment);
            return $this->_fileFactory->create(
                'packingslip' . $this->_objectManager->get(
                    'Magento\Framework\Stdlib\DateTime\DateTime'
                )->date(
                    'Y-m-d_H-i-s'
                ) . '.pdf',
                $pdf->render(),
                DirectoryList::VAR_DIR,
                'application/pdf'
            );
        } else {
            $this->_forward('noroute');
        }
    }
}
