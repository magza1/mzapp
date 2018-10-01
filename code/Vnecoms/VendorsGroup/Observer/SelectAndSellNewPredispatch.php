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

class SelectAndSellNewPredispatch implements ObserverInterface
{
        /**
     * @var \Vnecoms\VendorsGroup\Helper\Data
     */
    protected $_groupHelper;
    
    /**
     * @var \Vnecoms\Vendors\Model\Session
     */
    protected $_vendorSession;
    
    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $_redirect;
    
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;
    
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    
    /**
     * Constructor
     * 
     * @param \Vnecoms\VendorsGroup\Helper\Data $groupHelper
     * @param \Vnecoms\Vendors\Model\Session $vendorSession
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Vnecoms\VendorsGroup\Helper\Data $groupHelper,
        \Vnecoms\Vendors\Model\Session $vendorSession,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_groupHelper = $groupHelper;
        $this->_vendorSession = $vendorSession;
        $this->_redirect = $redirect;
        $this->_productCollectionFactory = $productCollectionFactory;
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
        $groupId = $this->_vendorSession->getVendor()->getGroupId();
        $productLimit = $this->_groupHelper->getProductLimit($groupId);
        if($productLimit){
            $collection = $this->_productCollectionFactory->create();
            $collection->addAttributeToFilter('vendor_id', $this->_vendorSession->getVendor()->getId());
            if(($productCount = $collection->count()) >= $productLimit){
                $controllerAction = $observer->getControllerAction();
                $this->messageManager->addError(__("You have total %1 products which is reached your limitations. You can not add more products.", $productCount));
                $this->_redirect->redirect($controllerAction->getResponse(), 'catalog/product');
                $controllerAction->getActionFlag()->set('', \Magento\Framework\App\ActionInterface::FLAG_NO_DISPATCH,true);
                return;
            }
        }
    }
}
