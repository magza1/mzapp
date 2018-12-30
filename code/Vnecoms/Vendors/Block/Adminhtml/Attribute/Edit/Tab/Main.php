<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product attribute add/edit form main tab
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Vnecoms\Vendors\Block\Adminhtml\Attribute\Edit\Tab;

use Magento\Eav\Block\Adminhtml\Attribute\Edit\Main\AbstractMain;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Main extends AbstractMain
{
    /**
     * Adding product form elements for editing attribute
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $attributeObject = $this->getAttributeObject();
        $frontendInputElm = $this->getForm()->getElement('frontend_input');
        $frontendInputElm->setData('label', __('Input type'));
        $frontendInputElm->setData('label', __('Input type'));
        
        $fieldset = $this->getForm()->getElement('base_fieldset');
        $fieldset->removeField('is_unique');
        
        /* Add new field types */
        $frontendInputElm = $this->getForm()->getElement('frontend_input');
        $fieldTypes = $this->_inputTypeFactory->create()->toOptionArray();
        $fieldTypes = array_merge($fieldTypes, $this->getAdditionalFieldTypes());
        $frontendInputElm->setData('values', $fieldTypes);
        
        $fieldset->addField(
            'default_value_file',
            'text',
            [
                'name' => 'default_value_file',
                'label' => __('Allowed file extensions'),
                'title' => __('Allowed file extensions'),
                'note' => __('Separated by comma (e.g. jpg,png,gif)'),
                'value' => $attributeObject->getDefaultValue()
            ],
            'default_value_textarea'
        );
        $yesno = $this->_yesnoFactory->create()->toOptionArray();
        
        $fieldset->addField(
            'is_used_in_profile_form',
            'select',
            [
                'name' => 'is_used_in_profile_form',
                'label' => __('Is Used in Profile Form'),
                'title' => __('Is Used in Profile Form'),
                'values' => $yesno
            ],
            'frontend_class'
        );
        $fieldset->addField(
            'hide_from_vendor_panel',
            'select',
            [
                'name' => 'hide_from_vendor_panel',
                'label' => __('Hide This Field From Vendor Panel'),
                'title' => __('Hide This Field From Vendor Panel'),
                'values' => $yesno,
                'note' => __('If you set this to Yes, this attribute will be hid from vendor profile in vendor panel.'),
            ],
            'frontend_class'
        );
        $fieldset->addField(
            'is_used_in_registration_form',
            'select',
            [
                'name' => 'is_used_in_registration_form',
                'label' => __('Is Used in Registration Form'),
                'title' => __('Is Used in Registration Form'),
                'values' => $yesno
            ],
            'frontend_class'
        );
        $fieldset->addField(
            'sort_order',
            'text',
            [
                'name' => 'sort_order',
                'label' => __('Position'),
                'title' => __('Position'),
                'value' => $attributeObject->getSortOrder()
            ]
        );
        
        return $this;
    }

    /**
     * Retrieve additional element types for product attributes
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        return ['apply' => 'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Apply'];
    }
    
    public function getAdditionalFieldTypes()
    {
        return [
            ['value' => 'file', 'label' => __('File')],
        ];
    }
}
