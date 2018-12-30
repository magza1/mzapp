<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsGroup\Observer;

use Magento\Framework\Event\ObserverInterface;
use Vnecoms\VendorsProduct\Helper\Data as ProductHelper;
use Vnecoms\VendorsPriceComparison\Helper\Data as PriceComparisonHelper;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class GroupSaveAfter implements ObserverInterface
{
    /**
     * @var \Vnecoms\VendorsGroup\Model\ConfigFactory
     */
    protected $_configFactory;
    
    public function __construct(
        \Vnecoms\VendorsGroup\Model\ConfigFactory $configFactory
    ) {
        $this->_configFactory = $configFactory;
    }
    /**
     * Save save group additional data
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return self
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Vnecoms\Vendors\Model\Group*/
        $group = $observer->getObject();
        $configData = $group->getData('advanced_config');
        if (!$configData || !is_array($configData)) {
            return;
        }
        
        foreach ($configData as $sectionName => $sectionData) {
            foreach ($sectionData as $fieldName => $value) {
                $resourceId = $sectionName."/".$fieldName;
                $config = $this->_configFactory->create();
                /*Load Resource By Resources Id*/
                $collection = $config->getCollection()
                    ->addFieldToFilter('resource_id', $resourceId)
                    ->addFieldToFilter('group_id', $group->getId());
                
                if ($collection->count()) {
                    $config     = $collection->getFirstItem();
                }
                
                $config->setGroupId($group->getId());
                $config->setResourceId($resourceId);
                $config->setValue($value);
                $config->save();
            }
        }
        return $this;
    }
}
