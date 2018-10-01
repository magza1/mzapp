<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsSales\Controller\Adminhtml\Sales\Shipment;

use Magento\Framework\DataObject;

class ShipmentLoader extends DataObject
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    /**
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     */
    protected $shipmentRepository;
    
    /**
     * @var \Vnecoms\VendorsSales\Model\Order\ShipmentFactory
     */
    protected $shipmentFactory;
    
    /**
     * @var \Magento\Sales\Model\Order\Shipment\TrackFactory
     */
    protected $trackFactory;
    
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;
    
    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository
     * @param  \Vnecoms\VendorsSales\Model\Order\ShipmentFactory $shipmentFactory
     * @param \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Vnecoms\VendorsSales\Model\Order\ShipmentFactory $shipmentFactory,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        $this->messageManager = $messageManager;
        $this->registry = $registry;
        $this->shipmentRepository = $shipmentRepository;
        $this->shipmentFactory = $shipmentFactory;
        $this->trackFactory = $trackFactory;
        $this->orderRepository = $orderRepository;
        parent::__construct($data);
    }
    
    /**
     * Initialize shipment items QTY
     *
     * @return array
     */
    protected function getItemQtys()
    {
        $data = $this->getShipment();
    
        return isset($data['items']) ? $data['items'] : [];
    }
    
    
    /**
     * Initialize shipment model instance
     *
     * @return bool|\Magento\Sales\Model\Order\Shipment
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function load()
    {
        $shipment = false;
        $orderId = $this->getOrderId();
        $shipmentId = $this->getShipmentId();
        if ($shipmentId) {
            $shipment = $this->shipmentRepository->get($shipmentId);
        } elseif ($orderId) {
            $vendorOrder = $this->getVendorOrder();
            
            $order = $this->orderRepository->get($orderId);
    
            /**
             * Check order existing
            */
            if (!$order->getId()) {
                $this->messageManager->addError(__('The order no longer exists.'));
                return false;
            }
            /**
             * Check shipment is available to create separate from invoice
             */
            if ($order->getForcedShipmentWithInvoice()) {
                $this->messageManager->addError(__('Cannot do shipment for the order separately from invoice.'));
                return false;
            }
            /**
             * Check shipment create availability
             */
            if (!$vendorOrder->canShip()) {
                $this->messageManager->addError(__('Cannot do shipment for the order.'));
                return false;
            }
    
            $shipment = $this->shipmentFactory->createVendorShipment(
                $vendorOrder,
                $this->getItemQtys(),
                $this->getTracking()
            );
        }
    
        $this->registry->register('current_shipment', $shipment);
        return $shipment;
    }
}
