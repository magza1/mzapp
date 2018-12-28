<?php

namespace Vnecoms\VendorsLayerNavigation\Model\ResourceModel\Layer\Filter;

class Price extends \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price
{
    protected function getAllowState($reFormat=false)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $allowState = $om->create('Vnecoms\VendorsProduct\Helper\Data')->getAllowedApprovalStatus();

        if($reFormat) {
            return implode($allowState, ', ');
        }

        return $allowState;
    }
    /**
     * Retrieve clean select with joined price index table
     *
     * @return \Magento\Framework\DB\Select
     */
    protected function _getSelect()
    {
        $vendorId = \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magento\Framework\Registry')->registry('vendor')->getId();

        $storeId = \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();

        $select = parent::_getSelect();
        $wherePart = $select->getPart(\Magento\Framework\DB\Select::WHERE);

        //check flat product mode
        $flatMode = \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getValue('catalog/frontend/flat_catalog_product');

        //  var_dump($wherePart);
        //remove vendor part of where part
        foreach($wherePart as $id => $where) {

            if(preg_match("/\`vendor_id\`/is",$where)){
                unset($wherePart[$id]);
            }

            if(preg_match("/vendor_id/is",$where)){
                unset($wherePart[$id]);
            }
            if ($flatMode) {
                if(preg_match("/approval/is",$where)){
                    unset($wherePart[$id]);
                }

                if(preg_match("/\`approval\`/is",$where)){
                    unset($wherePart[$id]);
                }
            }

        }

        if($flatMode)
        {
            $firstWhere = current($wherePart);
            $wherePart[key($wherePart)] = trim($firstWhere, 'AND ');
        }


        $select->reset(\Magento\Framework\DB\Select::WHERE);
        $fromPart = $select->getPart(\Magento\Framework\DB\Select::FROM);

        $select->setPart(\Magento\Framework\DB\Select::WHERE, $wherePart);
        if(!isset($fromPart['product_entity']) && $vendorId) {
            $select->join(
                ['product_entity'=>$this->getTable('catalog_product_entity')],
                "product_entity.entity_id = e.entity_id AND product_entity.vendor_id = '".$vendorId."'",
                []
            );
        }

        if ($flatMode) {
            if(!isset($fromPart['at_approval'])) {
                $approvalValue = '('.$this->getAllowState(true).')';
                $select->join(
                    ['at_approval'=>$this->getTable('catalog_product_entity_int')],
                    "at_approval.entity_id = e.entity_id AND at_approval.attribute_id = '".$this->getIdOfAttributeCode('catalog_product','approval')."'"
                    ." AND at_approval.value IN".$approvalValue." AND at_approval.store_id = '0'", //@todo dont know why need to 0
                    []
                );
            }
        }

        return $select;
    }

    public function getIdOfAttributeCode($entityCode, $code)
    {
        return \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magento\Eav\Model\ResourceModel\Entity\Attribute')
            ->getIdByCode($entityCode,$code);
    }
}
