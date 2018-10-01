<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsSales\Model\Order\Email\Sender;

use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order\Creditmemo;
use Vnecoms\VendorsSales\Model\Order;
use Vnecoms\VendorsSales\Model\Order\Email\Container\CreditmemoIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Vnecoms\VendorsSales\Model\Order\Email\Sender;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo as CreditmemoResource;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\Event\ManagerInterface;
use Vnecoms\Vendors\Model\VendorFactory as VendorFactory;

/**
 * Class CreditmemoSender
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreditmemoSender extends Sender
{
    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var CreditmemoResource
     */
    protected $creditmemoResource;

    /**
     * Global configuration storage.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $globalConfig;

    /**
     * @var Renderer
     */
    protected $addressRenderer;

    /**
     * @var \Vnecoms\Vendors\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Application Event Dispatcher
     *
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @param Template $templateContainer
     * @param CreditmemoIdentity $identityContainer
     * @param Order\Email\SenderBuilderFactory $senderBuilderFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param PaymentHelper $paymentHelper
     * @param CreditmemoResource $creditmemoResource
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
     * @param Renderer $addressRenderer
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        Template $templateContainer,
        CreditmemoIdentity $identityContainer,
        \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory,
        \Psr\Log\LoggerInterface $logger,
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper,
        CreditmemoResource $creditmemoResource,
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig,
        ManagerInterface $eventManager,
        VendorFactory $vendorFactory,
        \Magento\Framework\ObjectManagerInterface $objectManagerInterface
    ) {
        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory, $logger, $addressRenderer);
        $this->paymentHelper = $paymentHelper;
        $this->creditmemoResource = $creditmemoResource;
        $this->globalConfig = $globalConfig;
        $this->addressRenderer = $addressRenderer;
        $this->eventManager = $eventManager;
        $this->vendorFactory = $vendorFactory;
        $this->_objectManager = $objectManagerInterface;
    }

    /**
     * Sends order creditmemo email to the customer.
     *
     * Email will be sent immediately in two cases:
     *
     * - if asynchronous email sending is disabled in global settings
     * - if $forceSyncMode parameter is set to TRUE
     *
     * Otherwise, email will be sent later during running of
     * corresponding cron job.
     *
     * @param Creditmemo $creditmemo
     * @param bool $forceSyncMode
     * @return bool
     */
    public function send(Creditmemo $creditmemo, $forceSyncMode = false)
    {
        $creditmemo->setSendEmail(true);
        $vendorOrder = $this->_objectManager->create('Vnecoms\VendorsSales\Model\Order')->load($creditmemo->getVendorOrderId());
        if (!$this->globalConfig->getValue('sales_email/general/async_sending') || $forceSyncMode) {
            $order = $creditmemo->getOrder();
            $vendor =  $vendorOrder->getVendor();
            $transport = [
                'vendor' => $vendor,
                'vendorOrder' => $vendorOrder,
                'order' => $order,
                'creditmemo' => $creditmemo,
                'comment' => $creditmemo->getCustomerNoteNotify() ? $creditmemo->getCustomerNote() : '',
                'billing' => $order->getBillingAddress(),
                'payment_html' => $this->getPaymentHtml($vendorOrder),
                'store' => $order->getStore(),
                'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
                'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
            ];

            $this->eventManager->dispatch(
                'email_creditmemo_set_template_vars_before',
                ['sender' => $this, 'transport' => $transport]
            );

            $this->templateContainer->setTemplateVars($transport);
            $this->checkAndSend($vendorOrder);
        }


        return false;
    }

    /**
     * Get payment info block as html
     *
     * @param Order $order
     * @return string
     */
    protected function getPaymentHtml(Order $vendorOrder)
    {
        $order = $vendorOrder->getOrder();
        return $this->paymentHelper->getInfoBlockHtml(
            $order->getPayment(),
            $this->identityContainer->getStore()->getStoreId()
        );
    }
}
