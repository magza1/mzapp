<?php
namespace Vnecoms\VendorsGroup\Observer;

use Magento\Framework\Event\ObserverInterface;
use Vnecoms\VendorsProduct\Helper\Data as ProductHelper;
use Vnecoms\VendorsPriceComparison\Helper\Data as PriceComparisonHelper;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class DomainPermission implements ObserverInterface
{
    /**
    * @var \Vnecoms\VendorsGroup\Helper\Data
    */
    protected $groupHelper;
    
    /**
     * @param unknown $groupHelper
     */
    public function __construct(
        \Vnecoms\VendorsGroup\Helper\Data $groupHelper
    ) {
        $this->groupHelper = $groupHelper;
    }
    
    /**
     * Set domain permission
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return self
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $groupId = $observer->getVendor()->getGroupId();
        $transport = $observer->getTransport();
        
        $data = [
            'can_use_domain' => $this->groupHelper->canUseDomain($groupId),
            'can_use_subdomain' => $this->groupHelper->canUseSubdomain($groupId),
            'can_change_subdomain' => $this->groupHelper->canEditSubdomain($groupId),
        ];
        $transport->setData($data);
    }
}
