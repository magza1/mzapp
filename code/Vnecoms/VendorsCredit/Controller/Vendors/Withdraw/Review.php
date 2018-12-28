<?php

namespace Vnecoms\VendorsCredit\Controller\Vendors\Withdraw;

class Review extends \Vnecoms\Vendors\Controller\Vendors\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    protected $_aclResource = 'Vnecoms_VendorsCredit::credit_withdraw';
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    
    /**
     * @var \Vnecoms\Credit\Model\CreditFactory
     */
    protected $_creditFactory;
    
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
        \Vnecoms\Credit\Model\CreditFactory $creditAccountFactory
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->_creditFactory = $creditAccountFactory;
    }

    
    /**
     * @return void
     */
    public function execute()
    {
        $params  = $this->_session->getWithdrawalParams();
        try {
            if (!isset($params['method']) || !isset($params['amount'])) {
                throw new \Exception(__('The params is not valid.'));
            }
            
            $methodCode = $params['method'];
            $methodModelName = $this->_scopeConfig->getValue('withdrawal_methods/'.$methodCode.'/model');
            
            $method = $this->_objectManager->create($methodModelName);
                        

            $this->_coreRegistry->register('current_method', $method);
            $this->_coreRegistry->register('withdrawal_method', $method);
            $this->_coreRegistry->register('amount', $params['amount']);
            $this->_coreRegistry->register('step', 'review');
            
            $this->_initAction();
            $title = $this->_view->getPage()->getConfig()->getTitle();
            $title->prepend(__("Credit"));
            $title->prepend(__("Withdraw Funds"));
            $title->prepend(__("Review"));
            $this->_view->renderLayout();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $this->_redirect('*/*');
        }
    }
}
