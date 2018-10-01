<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsSales\Model\Order;

/**
 * Factory class for @see \Magento\Sales\Api\Data\ShipmentInterface
 */
class ShipmentFactory extends \Magento\Sales\Model\Order\ShipmentFactory
{
    /**
     * Creates shipment instance with specified parameters.
     *
     * @param \Vnecoms\VendorsSales\Model\Order $order
     * @param array $items
     * @param array|null $tracks
     * @return \Magento\Sales\Api\Data\ShipmentInterface
     */
    public function createVendorShipment(\Vnecoms\VendorsSales\Model\Order $vendorOrder, array $items = [], $tracks = null)
    {
        $order = $vendorOrder->getOrder();
        $shipment = $this->prepareVendorsItems($this->converter->toShipment($order), $vendorOrder, $items);
    
        if ($tracks) {
            $shipment = $this->prepareTracks($shipment, $tracks);
        }
    
        return $shipment;
    }
    
    /**
     * Adds items to the shipment.
     *
     * @param \Magento\Sales\Api\Data\ShipmentInterface $shipment
     * @param \Magento\Sales\Model\Order $order
     * @param array $items
     * @return \Magento\Sales\Api\Data\ShipmentInterface
     */
    protected function prepareVendorsItems(
        \Magento\Sales\Api\Data\ShipmentInterface $shipment,
        \Vnecoms\VendorsSales\Model\Order $vendorOrder,
        array $items = []
    ) {
        $totalQty = 0;

        $order = $vendorOrder->getOrder();
        
        foreach ($order->getAllItems() as $orderItem) {
            if (!$this->canShipItem($orderItem, $items)) {
                continue;
            }

            /** @var \Magento\Sales\Model\Order\Shipment\Item $item */
            $item = $this->converter->itemToShipmentItem($orderItem);

            if ($orderItem->isDummy(true)) {
                $qty = 0;

                if (isset($items[$orderItem->getParentItemId()])) {
                    $productOptions = $orderItem->getProductOptions();

                    if (isset($productOptions['bundle_selection_attributes'])) {
                        $bundleSelectionAttributes = unserialize(
                            $productOptions['bundle_selection_attributes']
                        );

                        if ($bundleSelectionAttributes) {
                            $qty = $bundleSelectionAttributes['qty'] * $items[$orderItem->getParentItemId()];
                            $qty = min($qty, $orderItem->getSimpleQtyToShip());

                            $item->setQty($qty);
                            $shipment->addItem($item);

                            continue;
                        } else {
                            $qty = 1;
                        }
                    }
                } else {
                    $qty = 1;
                }
            } else {
                if (isset($items[$orderItem->getId()])) {
                    $qty = min($items[$orderItem->getId()], $orderItem->getQtyToShip());
                } elseif (!count($items)) {
                    $qty = $orderItem->getQtyToShip();
                } else {
                    continue;
                }
            }
            /**
             * If the item is not item of current vendor just set qty = 0.
             */
            if ($vendorOrder->getVendorId() != $orderItem->getVendorId()) {
                $qty = 0;
            }
            
            $totalQty += $qty;

            $item->setQty($qty);
            $shipment->addItem($item);
        }

        return $shipment->setTotalQty($totalQty);
    }
}
