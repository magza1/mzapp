<?php

namespace Vnecoms\VendorsSales\Controller\Vendors\Shipment;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Vnecoms\Vendors\App\Action\Context;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory;

class MassPrintShippingLabel extends \Vnecoms\VendorsSales\Controller\Vendors\Order\AbstractMassAction
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    protected $_aclResource = 'Vnecoms_VendorsSales::sales_shipments';
    
    /**
     * @var LabelGenerator
     */
    protected $labelGenerator;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param FileFactory $fileFactory
     * @param LabelGenerator $labelGenerator
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        FileFactory $fileFactory,
        LabelGenerator $labelGenerator,
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->fileFactory = $fileFactory;
        $this->labelGenerator = $labelGenerator;
        parent::__construct($context, $filter);
    }

    /**
     * Batch print shipping labels for whole shipments.
     * Push pdf document with shipping labels to user browser
     *
     * @param AbstractCollection $collection
     * @return ResponseInterface|ResultInterface
     */
    protected function massAction(AbstractCollection $collection)
    {
        $labelsContent = [];

        if ($collection->getSize()) {
            /** @var \Magento\Sales\Model\Order\Shipment $shipment */
            foreach ($collection as $shipment) {
                $labelContent = $shipment->getShippingLabel();
                if ($labelContent) {
                    $labelsContent[] = $labelContent;
                }
            }
        }

        if (!empty($labelsContent)) {
            $outputPdf = $this->labelGenerator->combineLabelsPdf($labelsContent);
            return $this->fileFactory->create(
                'ShippingLabels.pdf',
                $outputPdf->render(),
                DirectoryList::VAR_DIR,
                'application/pdf'
            );
        }

        $this->messageManager->addError(__('There are no shipping labels related to selected shipments.'));
        return $this->resultRedirectFactory->create()->setPath('sales/order_shipment/');
    }
}
