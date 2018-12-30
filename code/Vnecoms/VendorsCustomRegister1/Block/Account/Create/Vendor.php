<?php

namespace Vnecoms\VendorsCustomRegister1\Block\Account\Create;

class Vendor extends \Vnecoms\Vendors\Block\Account\Create\Vendor
{
    /**
     * Get fieldset blocks
     * @return array:
     */
    public function getFieldsetBlocks()
    {
        parent::getFieldsetBlocks();
        foreach($this->_fieldsets as $fieldsetBlock){
            $fieldsetBlock->setTemplate('Vnecoms_VendorsCustomRegister1::account/create/fieldset.phtml');
        }

        return $this->_fieldsets;
    }
}
