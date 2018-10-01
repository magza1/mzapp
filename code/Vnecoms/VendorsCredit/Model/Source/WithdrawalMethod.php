<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsCredit\Model\Source;

use Vnecoms\VendorsCredit\Model\Withdrawal;

class WithdrawalMethod extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
    }
    
    /**
     * Options array
     *
     * @var array
     */
    protected $_options = null;
    
    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        
        if ($this->_options === null) {
            $this->_options = [];
            $availableMethods = $this->_scopeConfig->getValue('withdrawal_methods');
            foreach ($availableMethods as $code => $methodInfo) {
                $title = __(isset($methodInfo['title'])?$methodInfo['title']:'');
                if (!isset($methodInfo['active']) || !$methodInfo['active']) {
                    $title .= ' [disabled]';
                }
                $this->_options[] = ['label' => $title, 'value' => $code];
            }
        }
        return $this->_options;
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $_options = [];
        foreach ($this->getAllOptions() as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }
    
    
    /**
     * Get options as array
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}
