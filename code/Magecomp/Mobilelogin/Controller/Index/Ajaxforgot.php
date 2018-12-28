<?php
 
namespace Magecomp\Mobilelogin\Controller\Index;
use Magento\Framework\App\Action\Context;
use Magecomp\Mobilelogin\Model\ForgototpmodelFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Controller\ResultFactory;
class Ajaxforgot extends \Magento\Framework\App\Action\Action
{
   
    protected $_ForgototpmodelFactory;
	protected $_CustomerFactory;
	public function __construct(
		Context $context,
		ForgototpmodelFactory $ForgototpmodelFactory,		
		CustomerFactory $CustomerFactory
	){
       $this->_ForgototpmodelFactory = $ForgototpmodelFactory;
	   $this->_CustomerFactory = $CustomerFactory;
        parent::__construct($context);
    }
   public function execute()
    {
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		$logger->info("hi1");
		$helperData = $this->_objectManager->create('Magecomp\Mobilelogin\Helper\Data');		
		$randomCode = $helperData->generateRandomString();
		$message = $helperData->getForgotOtpMessage($randomCode);
		$mobile = $this->getRequest()->get('mobile');
		$objDate = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
		$date = $objDate->gmtDate();
		$customerCount = $this->_CustomerFactory->create()->getCollection()->addFieldToFilter("mobilenumber", $mobile);
		$response = "false";
		if(count($customerCount) == 1 ){
			$logger->info("hi2");
				$otpModels = $this->_ForgototpmodelFactory->create();		
				$collection = $otpModels->getCollection();
				$collection->addFieldToFilter('mobile', $mobile);
				$customer = $customerCount->getFirstItem();
				if(count($collection) == 0 ){
					$logger->info("hi3");
					$forgotTable = $this->_ForgototpmodelFactory->create();		
					$forgotTable->setRandomCode($randomCode);
					$forgotTable->setCreatedTime($date);
					$forgotTable->setMobile($mobile);
					$forgotTable->setEmail($customer->getEmail());
					$forgotTable->setIpaddress($_SERVER['REMOTE_ADDR']);
					$forgotTable->setIsVerify(0);
					$forgotTable->save();
				}else{
					$logger->info("hi4");
					$forgotTable = $this->_ForgototpmodelFactory->create()->load($mobile,'mobile');;		
					$forgotTable->setRandomCode($randomCode);
					$forgotTable->setCreatedTime($date);
					$forgotTable->setMobile($mobile);
					$forgotTable->setEmail($customer->getEmail());
					$forgotTable->setIpaddress($_SERVER['REMOTE_ADDR']);
					$forgotTable->setIsVerify(0);
					$forgotTable->save();
				}
				 $helperData->curlApiCall($message,$mobile);
			$logger->info("hi5");
			/*$response = "true";
			$resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
	        $resultJson->setData($response);
			$logger->info("hi7");*/
			echo "true";
    	   // return $resultJson;


		}
		else {
			/*$logger->info("hi6");
			$resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
			$resultJson->setData($response);
			return $resultJson;*/
			echo "false";
		}


    }
}