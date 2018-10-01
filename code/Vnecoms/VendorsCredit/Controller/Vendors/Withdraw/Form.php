<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsCredit\Controller\Vendors\Withdraw;

class Form extends \Vnecoms\Vendors\Controller\Vendors\Action
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    
    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Date $dateFilter
     */
    public function __construct(
        \Vnecoms\Vendors\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
    }
    
    /**
     * @return void
     */
    public function execute()
    {
        $this->_coreRegistry->register('step', 'withdrawal_form');
        
        $method = $this->getRequest()->getParam('method');
        $methodModelName = $this->_scopeConfig->getValue('withdrawal_methods/'.$method.'/model');
        $availableMethods = $this->_scopeConfig->getValue('withdrawal_methods');
        if (!$method ||
            !$methodModelName ||
            !isset($availableMethods[$method]) ||
            !is_array($availableMethods[$method])
        ) {
            $this->messageManager->addError(__("Withdrawal method is not valid."));
            return $this->_redirect('*/*');
        }
        try {
            $method = $this->_objectManager->create($methodModelName);
            if (!$method->isActive()) {
                throw new \Exception(__("Withdrawal method is not available."));
            }
            if (!$method->isEnteredMethodInfo($this->_session->getVendor()->getId())) {
                $this->messageManager->addError(__("You need to enter all of your %1 info to use this method.", $method->getTitle()));
                return $this->_redirect('config/index/edit', ['section'=>'withdrawal']);
            }
            
            $this->_coreRegistry->register('current_method', $method);
            $this->_coreRegistry->register('withdrawal_method', $method);
            $this->_initAction();
            $title = $this->_view->getPage()->getConfig()->getTitle();
            $title->prepend(__("Credit"));
            $title->prepend(__("Withdraw Funds"));
            $this->_view->renderLayout();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $this->_redirect('*/*');
        }
    }
}
