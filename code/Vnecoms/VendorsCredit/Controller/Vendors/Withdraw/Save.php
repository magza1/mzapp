<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsCredit\Controller\Vendors\Withdraw;

use Vnecoms\VendorsCredit\Model\Withdrawal as WithdrawalModel;
use Vnecoms\VendorsCredit\Model\CreditProcessor\Withdraw as WithdrawProcessor;

class Save extends \Vnecoms\Vendors\Controller\Vendors\Action
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
     * @var \Vnecoms\Credit\Model\Processor
     */
    protected $_creditProcessor;
    
    /**
     * @var \Vnecoms\Credit\Model\CreditFactory
     */
    protected $_creditAccountFactory;
    
    /**
     * @var \Vnecoms\VendorsCredit\Helper\Data
     */
    protected $_vendorCreditHelper;
    
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
        \Vnecoms\Credit\Model\Processor $creditProcessor,
        \Vnecoms\Credit\Model\CreditFactory $creditAccountFactory,
        \Vnecoms\VendorsCredit\Model\WithdrawalFactory $withdrawalFactory,
        \Vnecoms\VendorsCredit\Helper\Data $vendorCreditHelper
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->_withdrawalFactory = $withdrawalFactory;
        $this->_creditProcessor = $creditProcessor;
        $this->_creditAccountFactory = $creditAccountFactory;
        $this->_vendorCreditHelper = $vendorCreditHelper;
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
            
            $vendorId = $this->_session->getVendor()->getId();
            $method = $this->_objectManager->create($methodModelName);
            $amount = $params['amount'];
            $fee = $method->calculateFee($amount);
            $netAmount = $amount - $fee;
            
            $withdrawal = $this->_withdrawalFactory->create();
            $withdrawal->setData([
                'vendor_id' => $vendorId,
                'method' => $methodCode,
                'method_title' => $method->getTitle(),
                'amount' => $amount,
                'fee' => $fee,
                'net_amount' => $netAmount,
                'additional_info' => serialize($method->getVendorAccountInfo($vendorId)),
                'status' => WithdrawalModel::STATUS_PENDING,
            ]);

            $withdrawal->save();
            
            /*Send new withdrawal notification email to vendor*/
            $this->_vendorCreditHelper->sendNewWithdrawalNotificationToVendor($withdrawal, $this->_session->getVendor());
            
            /*Send new withdrawal notification email to admin*/
            $this->_vendorCreditHelper->sendNewWithdrawalNotificationToAdmin($withdrawal, $this->_session->getVendor());
            
            /*Reset session*/
            $this->_session->setWithdrawalParams(null);
                        
            /*Create transaction to subtract the credit.*/
            $creditAccount = $this->_creditAccountFactory->create();
            $creditAccount->loadByCustomerId($this->_session->getCustomerId());
            $data = [
                'vendor' => $this->_session->getVendor(),
                'type' => WithdrawProcessor::TYPE,
                'amount' => $amount,
                'withdrawal_request' => $withdrawal,
            ];
            $this->_creditProcessor->process($creditAccount, $data);
            
            /* Send notification email here */
            
            $this->messageManager->addSuccess(__("Your withdrawal request has been submited."));
            return $this->_redirect('credit/withdraw/history');
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $this->_redirect('*/*');
        }
    }
}
