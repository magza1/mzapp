<?php

namespace Vnecoms\VendorsCredit\Controller\Vendors\Withdraw;

class FormPost extends \Vnecoms\Vendors\Controller\Vendors\Action
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
        $methodCode = $this->getRequest()->getParam('method');
        $methodModelName = $this->_scopeConfig->getValue('withdrawal_methods/'.$methodCode.'/model');
        $availableMethods = $this->_scopeConfig->getValue('withdrawal_methods');
        if (!$methodCode ||
            !$methodModelName ||
            !isset($availableMethods[$methodCode]) ||
            !is_array($availableMethods[$methodCode])
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
                $this->messageManager->addError(__("You need to enter your %1 info to use this method.", $method->getTitle()));
                return $this->_redirect('config/index/edit', ['section'=>'withdrawal']);
            }
            
            /*Validate withdrawal amount*/
            $msg = '';
            $amount = $this->getRequest()->getParam('amount', 0);
            
            $creditAccount = $this->_creditFactory->create();
            $creditAccount->loadByCustomerId($this->_session->getCustomer()->getId());
            $creditBalance = $creditAccount->getCredit();
            if ($amount > $creditBalance) {
                $msg = __("Your withdrawal amount must be less than your balance amount.");
            }
            
            $min = $method->getMinValue();
            $max = $method->getMaxValue();
            
            if ($amount == 0 || ($min != 0 && $amount < $min) || ($max > 0 && $amount > $max)) {
                if ($amount == 0) {
                    $msg = __("Your withdrawal amount must be greater than zero");
                } else {
                    $msg = __("Your withdrawal amount must be between %1 - %2", $min, $max);
                }
            }
            
            
            if ($msg) {
                $this->messageManager->addError($msg);
                return $this->_redirect('credit/withdraw/form', ['method'=>$methodCode]);
            }
            
            $params = [
                'method' => $methodCode,
                'amount' => $amount,
            ];
            
            $this->_session->setData('withdrawal_params', $params);
            
            return $this->_redirect('credit/withdraw/review');
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $this->_redirect('*/*');
        }
    }
}
