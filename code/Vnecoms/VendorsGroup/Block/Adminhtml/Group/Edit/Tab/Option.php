<?php
namespace Vnecoms\VendorsGroup\Block\Adminhtml\Group\Edit\Tab;

use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Option extends Generic implements TabInterface
{
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Vnecoms\VendorsGroup\Helper\Data
     */
    protected $_groupHelper;
    
    /**
     * @var \Vnecoms\VendorsGroup\Model\ConfigFactory
     */
    protected $_configFactory;
    
   /**
    * Constructor
    *
    * @param \Magento\Backend\Block\Template\Context $context
    * @param \Magento\Framework\Registry $registry
    * @param \Magento\Framework\Data\FormFactory $formFactory
    * @param \Vnecoms\VendorsGroup\Helper\Data $groupHelper
    * @param array $data
    */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Vnecoms\VendorsGroup\Helper\Data $groupHelper,
        \Vnecoms\VendorsGroup\Model\ConfigFactory $configFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_groupHelper = $groupHelper;
        $this->_configFactory = $configFactory;
        
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare content for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabLabel()
    {
        return __('Advanced Options');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabTitle()
    {
        return __('Advanced Options');
    }

    /**
     * Returns status flag about this tab can be showed or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isHidden()
    {
        return false;
    }
    
    /**
     * Sort array by sort order
     *
     * @param unknown $a
     * @param unknown $b
     * @return number
     */
    public function sortArray($a, $b)
    {
        $aOrder = isset($a['sortOrder'])?$a['sortOrder']:0;
        $bOrder = isset($b['sortOrder'])?$b['sortOrder']:0;
        return ($aOrder < $bOrder) ? -1 : 1;
    }

    /**
     * @return Form
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $groupConfig = $this->_groupHelper->getGroupConfig();
        $model = $this->_coreRegistry->registry('current_group');
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('advancedgroup_');
        
        usort($groupConfig, [$this, 'sortArray']);
        
        foreach ($groupConfig as $group) {
            $groupId = $group['id'];
            $fieldset = $form->addFieldset($groupId.'_fieldset', ['legend' => __($group['label'])]);
            $fields = $group['fields'];
            usort($fields, [$this, 'sortArray']);
            foreach ($fields as $field) {
                $fieldId = $field['id'];
                $field = $fieldset->addField(
                    'vendorsgroup_'.$groupId.'_'.$fieldId,
                    $field['type'],
                    [
                        'name' => 'advanced_config['.$groupId.']['.$fieldId.']',
                        'label' => __($field['label']),
                        'title' => __($field['label']),
                        'required'  => isset($field['required']) && $field['required'],
                        'class' => isset($field['frontend_class']) && $field['frontend_class']?$field['frontend_class']:'',
                        'values'    => isset($field['source_model']) && $field['source_model']?$om->create($field['source_model'])->toOptionArray():null,
                        'note'      => isset($field['comment']) && $field['comment']?$field['comment']:'',
                    ]
                );
                
                $resourceId = $groupId.'/'.$fieldId;
                $field->setValue(
                    $this->_groupHelper->getConfig($resourceId, $model->getId())
                );
            }
        }
     
        
        
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
