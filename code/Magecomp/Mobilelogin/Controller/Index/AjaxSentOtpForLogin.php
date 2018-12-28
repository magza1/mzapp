<?php
namespace Magecomp\Mobilelogin\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magecomp\Mobilelogin\Model\LoginotpmodelFactory;
use Magento\Framework\Controller\ResultFactory; 
class AjaxSentOtpForLogin extends \Magento\Framework\App\Action\Action
{
	protected $_modelLoginOtpFactory;
	public $_helperdata;
	public function __construct(
		Context $context,
		LoginotpmodelFactory $modelLoginOtpFactory,
		\Magecomp\Mobilelogin\Helper\Data $helperData
	
	){
		$this->_modelLoginOtpFactory = $modelLoginOtpFactory;
		$this->_helperdata = $helperData;
        parent::__construct($context);
    }
	public function execute()
    {
			$return = $this->_helperdata->sendLoginOTPCode($this->getRequest()->get('mobile'));
				$resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
				$resultJson->setData($return);
				return $resultJson;
	
	}
}