<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsSales\Controller\Vendors\Order;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Vnecoms\Vendors\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Vnecoms\VendorsSales\Model\ResourceModel\Order\CollectionFactory;

class MassCancel extends \Vnecoms\VendorsSales\Controller\Vendors\Order\AbstractMassAction
{
    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param DateTime $dateTime
     * @param FileFactory $fileFactory
     *
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        DateTime $dateTime
    ) {
        $this->dateTime = $dateTime;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $filter);
    }
    /**
     * Cancel selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {

        $orderCollection = $this->collectionFactory->create()
            ->addFieldToFilter('entity_id', ['in' => $collection->getAllIds()]);

        $countCancelOrder = 0;
        foreach ($orderCollection as $order) {
            if (!$order->canCancel()) {
                continue;
            }
            $order->cancel();
            $order->save();
            $countCancelOrder++;
        }
        $countNonCancelOrder = $collection->count() - $countCancelOrder;

        if ($countNonCancelOrder && $countCancelOrder) {
            $this->messageManager->addError(__('%1 order(s) cannot be canceled.', $countNonCancelOrder));
        } elseif ($countNonCancelOrder) {
            $this->messageManager->addError(__('You cannot cancel the order(s).'));
        }

        if ($countCancelOrder) {
            $this->messageManager->addSuccess(__('We canceled %1 order(s).', $countCancelOrder));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }
}
