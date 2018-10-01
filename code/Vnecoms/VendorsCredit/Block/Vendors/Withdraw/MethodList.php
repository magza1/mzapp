<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Vnecoms\VendorsCredit\Block\Vendors\Withdraw;

/**
 * Vendor Notifications block
 */
class MethodList extends \Vnecoms\Vendors\Block\Vendors\AbstractBlock
{
    /**
     * @var \Vnecoms\Vendors\Model\Session
     */
    protected $_vendorSession;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Vnecoms\Vendors\Model\UrlInterface $url,
        \Vnecoms\Vendors\Model\Session $session,
        array $data = []
    ) {
        parent::__construct($context, $url, $data);
        $this->_url = $url;
        $this->_vendorSession = $session;
        $this->_objectManager = $objectManager;
    }
    
    /**
     * Get withdrawal methods
     * 
     * @throws \Exception
     * @return multitype:\Magento\Framework\mixed
     */
    public function getWithdrawalMethods(){
        $methodConfig = $this->_scopeConfig->getValue('withdrawal_methods');
        $withdawalMethods = [];
        foreach($methodConfig as $code => $config){
            if(!$config['model']) throw new \Exception(__("Model is not defined in the withdrawal method %1",$code));
            if(!isset($config['active']) || !$config['active']) continue;
            $withdawalMethods[$code] = $this->_objectManager->create($config['model']);
        }
        return $withdawalMethods;
    }
    
    /**
     * Get Vendor
     * 
     * @return \Vnecoms\Vendors\Model\Vendor
     */
    public function getVendor(){
        return $this->_vendorSession->getVendor();
    }
    
    /**
     * Get Withdrawal URL
     * 
     * @param \Vnecoms\VendorsCredit\Model\Withdrawal\Method\AbstractMethod $method
     * @return string
     */
    public function getWithdrawalUrl(\Vnecoms\VendorsCredit\Model\Withdrawal\Method\AbstractMethod $method){
        return $this->getUrl('credit/withdraw/form',['method'=>$method->getCode()]);
    }
}
