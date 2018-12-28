<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsGroup\Observer;

use Magento\Framework\Event\ObserverInterface;
use Vnecoms\VendorsProduct\Helper\Data as ProductHelper;
use Vnecoms\VendorsPriceComparison\Helper\Data as PriceComparisonHelper;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class CheckVendorsMenu implements ObserverInterface
{
    public function __construct(
        \Vnecoms\VendorsGroup\Helper\Data $groupHelper,
        \Vnecoms\Vendors\Model\Session $vendorSession,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_groupHelper = $groupHelper;
        $this->_vendorSession = $vendorSession;
        $this->_redirect = $redirect;
        $this->messageManager = $messageManager;
    }

    /**
     *
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return self
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $vendorGroupId = $this->_vendorSession->getVendor()->getGroupId();

        if(!$this->_groupHelper->canUseMessage($vendorGroupId)) {
            if ($observer->getResource() == 'Vnecoms_VendorsMessage::sales') {
                $observer->getResult()->setIsAllowed(false);
            }
        }

        if(!$this->_groupHelper->canUseCMS($vendorGroupId)) {
            if (strpos($observer->getResource(), 'Cms')) {
                $observer->getResult()->setIsAllowed(false);
            }
        }
        
        if(!$this->_groupHelper->canUseReport($vendorGroupId)) {

            if (strpos($observer->getResource(), 'VendorsReport')) {
                $observer->getResult()->setIsAllowed(false);
            }
        }

        if(!$this->_groupHelper->canUseProductImportExport($vendorGroupId)) {
            if (strpos($observer->getResource(), 'product_import_export')) {
                $observer->getResult()->setIsAllowed(false);
            }
        }

        if(!$this->_groupHelper->canUseStoreLocator($vendorGroupId)) {
            if (strpos($observer->getResource(), 'VendorsStoreLocator')) {
                $observer->getResult()->setIsAllowed(false);
            }
        }

        if(!$this->_groupHelper->canUseStoreLocator($vendorGroupId)) {
            if (strpos($observer->getResource(), 'VendorsSms')) {
                $observer->getResult()->setIsAllowed(false);
            }
        }

        if(!$this->_groupHelper->canUseCategory($vendorGroupId)) {
            if (strpos($observer->getResource(), 'catalog_category')) {
                $observer->getResult()->setIsAllowed(false);
            }
        }
    }
}
