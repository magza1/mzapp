<?php
namespace Magecomp\Mobilelogin\Block;
 
class Mobilelogin extends \Magento\Framework\View\Element\Template
{
    public function getMobileloginTxt()
    {
        return 'Mobilelogin';
    }
    public function getMinimumPasswordLength()
    {
        return $this->_scopeConfig->getValue(AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH);
    }
   
}