<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsCredit\Controller\Vendors\Withdraw\History;

class View extends \Vnecoms\Vendors\Controller\Vendors\Action
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    
    /**
     * @var \Vnecoms\Credit\Model\CreditFactory
     */
    protected $_withdrawalFactory;
    
    
    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Date $dateFilter
     */
    public function __construct(
        \Vnecoms\Vendors\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Vnecoms\VendorsCredit\Model\WithdrawalFactory $withdrawFactory
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->_withdrawalFactory = $withdrawFactory;
    }

    
    /**
     * @return void
     */
    public function execute()
    {
        $withdrawalId = $this->getRequest()->getParam('id', false);
        try {
            $withdrawal = $this->_withdrawalFactory->create()->load($withdrawalId);
            if (!$withdrawalId ||
              !$withdrawal->getId() ||
              $withdrawal->getVendorId() != $this->_session->getVendor()->getId()
            ) {
                throw new \Exception(__('The withdrawal request is not valid.'));
            }
            

            $this->_coreRegistry->register('current_withdrawal', $withdrawal);
            $this->_coreRegistry->register('withdrawal', $withdrawal);
            
            $this->_initAction();
            $title = $this->_view->getPage()->getConfig()->getTitle();
            $title->prepend(__("Credit"));
            $title->prepend(__("Withdraw"));
            $title->prepend(__("View Withdrawal Request"));
            $this->_view->renderLayout();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $this->_redirect('credit/withdraw/history');
        }
    }
}
