<?php

namespace Vnecoms\VendorsConfigApproval\Model;

use Vnecoms\VendorsConfigApproval\Model\Config;

class Transaction extends \Magento\Framework\DB\Transaction
{
    /**
     * (non-PHPdoc)
     * @see \Magento\Framework\DB\Transaction::save()
     */
    public function save()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Vnecoms\VendorsConfigApproval\Helper\Data $helper */
        $helper = $om->get('Vnecoms\VendorsConfigApproval\Helper\Data');
        
        $this->_startTransaction();
        $error = false;
        /** @var \Vnecoms\VendorsConfig\Helper\Data $configHelper */
        $configHelper = $om->get('Vnecoms\VendorsConfig\Helper\Data');
        
        $fieldsRestriction = $helper->getFieldsRestriction();
        try {
            foreach ($this->_objects as $object) {
                /*These attributes will not need to be approved*/
                if(!in_array($object->getPath(), $fieldsRestriction)){
                    $object->save();
                    continue;
                }
                $object->beforeSave();
                $data = $object->getData();
                if(!isset($data['value'])) continue;
                
                /* Check if the config is already pending for approval*/
                $configCollection = $om->create('Vnecoms\VendorsConfigApproval\Model\ResourceModel\Config\Collection');
                $configCollection->addFieldToFilter('vendor_id', $object->getVendorId())
                    ->addFieldToFilter('path', $object->getPath());
                
                $originValue = $configHelper->getVendorConfig($object->getPath(), $object->getVendorId());
                
                if($configCollection->count()){
                    $configUpdate = $configCollection->getFirstItem();
                    if($object->getValue() == $originValue){
                        $configUpdate->delete();
                    }else{
                        $configUpdate->setValue($object->getValue())->setStatus(Config::STATUS_PENDING)->save();
                    }
                }else{
                    if($object->getValue() == $originValue) continue;
                    $configUpdate = $om->create('Vnecoms\VendorsConfigApproval\Model\Config');
                    $configUpdate->setData([
                        'config_id' => $object->getData('config_id'),
                        'vendor_id' => $object->getData('vendor_id'),
                        'path'      => $object->getData('path'),
                        'value'     => $object->getData('value'),
                        'status'    => Config::STATUS_PENDING,
                    ])->save();
                }
            }
        } catch (\Exception $e) {
            $error = $e;
        }
    
        if ($error === false) {
            try {
                $this->_runCallbacks();
            } catch (\Exception $e) {
                $error = $e;
            }
        }
    
        if ($error) {
            $this->_rollbackTransaction();
            throw $error;
        } else {
            $this->_commitTransaction();
        }
    
        return $this;
    }
}
