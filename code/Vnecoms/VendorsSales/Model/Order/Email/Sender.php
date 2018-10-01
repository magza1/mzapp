<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsSales\Model\Order\Email;

use Vnecoms\VendorsSales\Model\Order as Order;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Address\Renderer;

abstract class Sender
{
    /**
     * @var \Magento\Sales\Model\Order\Email\SenderBuilderFactory
     */
    protected $senderBuilderFactory;

    /**
     * @var Template
     */
    protected $templateContainer;

    /**
     * @var IdentityInterface
     */
    protected $identityContainer;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var Renderer
     */
    protected $addressRenderer;

    /**
     * @param Template $templateContainer
     * @param IdentityInterface $identityContainer
     * @param SenderBuilderFactory $senderBuilderFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param Renderer $addressRenderer
     */
    public function __construct(
        Template $templateContainer,
        IdentityInterface $identityContainer,
        \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory,
        \Psr\Log\LoggerInterface $logger,
        Renderer $addressRenderer
    ) {
        $this->templateContainer = $templateContainer;
        $this->identityContainer = $identityContainer;
        $this->senderBuilderFactory = $senderBuilderFactory;
        $this->logger = $logger;
        $this->addressRenderer = $addressRenderer;
    }

    /**
     * @param Order $order
     * @return bool
     */
    protected function checkAndSend(Order $vendorOrder)
    {
        $order = $vendorOrder->getOrder();
        $this->identityContainer->setVendorId($vendorOrder->getVendorId());
        $this->identityContainer->setStore($order->getStore());
        if (!$this->identityContainer->isEnabled()) {
            return false;
        }
        $this->prepareTemplate($vendorOrder);

        /** @var SenderBuilder $sender */
        $sender = $this->getSender();

        try {
            $sender->send();
            $sender->sendCopyTo();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return true;
    }

    /**
     * @param Order $order
     * @return void
     */
    protected function prepareTemplate(Order $order)
    {
        $this->templateContainer->setTemplateOptions($this->getTemplateOptions());

        $templateId = $this->identityContainer->getTemplateId();

        $vendor = $this->vendorFactory->create()->load($order->getVendorId());
        $customerName = $vendor->getName();


        $this->identityContainer->setCustomerName($customerName);
        $this->identityContainer->setCustomerEmail($vendor->getEmail());
        $this->templateContainer->setTemplateId($templateId);
    }

    /**
     * @return Sender
     */
    protected function getSender()
    {
        return $this->senderBuilderFactory->create(
            [
                'templateContainer' => $this->templateContainer,
                'identityContainer' => $this->identityContainer,
            ]
        );
    }

    /**
     * @return array
     */
    protected function getTemplateOptions()
    {
        return [
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => $this->identityContainer->getStore()->getStoreId()
        ];
    }

    /**
     * @param Order $order
     * @return string|null
     */
    protected function getFormattedShippingAddress($order)
    {
        return $order->getIsVirtual()
            ? null
            : $this->addressRenderer->format($order->getShippingAddress(), 'html');
    }

    /**
     * @param Order $order
     * @return string|null
     */
    protected function getFormattedBillingAddress($order)
    {
        return $this->addressRenderer->format($order->getBillingAddress(), 'html');
    }
}
