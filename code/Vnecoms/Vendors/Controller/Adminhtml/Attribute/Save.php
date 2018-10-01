<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Vnecoms\Vendors\Controller\Adminhtml\Attribute;

use \Magento\Framework\Exception\AlreadyExistsException;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Vnecoms\Vendors\Controller\Adminhtml\Attribute
{
    /**
     * @var \Magento\Catalog\Model\Product\AttributeSet\BuildFactory
     */
    protected $buildFactory;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $productHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    private $attributeSetFactory;
    
    
    /**
     * @var \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory
     */
    protected $validatorFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory
     */
    protected $groupCollectionFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Catalog\Model\Product\AttributeSet\BuildFactory $buildFactory
     * @param \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory
     * @param \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory $validatorFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Catalog\Model\Product\AttributeSet\BuildFactory $buildFactory,
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory,
        \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory $validatorFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory,
        \Magento\Framework\Filter\FilterManager $filterManager,
        AttributeSetFactory $attributeSetFactory,
        \Magento\Catalog\Helper\Product $productHelper
    ) {
        parent::__construct($context, $coreRegistry, $resultPageFactory);
        $this->buildFactory = $buildFactory;
        $this->filterManager = $filterManager;
        $this->productHelper = $productHelper;
        $this->attributeFactory = $attributeFactory;
        $this->validatorFactory = $validatorFactory;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        
    }
    
    /**
     * Is access to section allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return parent::_isAllowed() && $this->_authorization->isAllowed('Vnecoms_Vendors::vendors_attributes');
    }
    
    
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $setId = $this->getRequest()->getParam('set');

            $redirectBack = $this->getRequest()->getParam('back', false);
            /* @var $model \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
            $model = $this->_objectManager->create(
                'Vnecoms\Vendors\Model\Entity\Attribute'
            )->setEntityTypeId(
                $this->_entityTypeId
            );

            $customerEntity = $model->getEntity();
            $attributeSetId = $customerEntity->getDefaultAttributeSetId();
            /** @var $attributeSet AttributeSet */
            $attributeSet = $this->attributeSetFactory->create();
            $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

            $attributeId = $this->getRequest()->getParam('attribute_id');

            $attributeCode = $this->getRequest()->getParam('attribute_code');
            $frontendLabel = $this->getRequest()->getParam('frontend_label');
            $attributeCode = $attributeCode ?: $this->generateCode($frontendLabel[0]);
            if (strlen($this->getRequest()->getParam('attribute_code')) > 0) {
                $validatorAttrCode = new \Zend_Validate_Regex(['pattern' => '/^[a-z][a-z_0-9]{0,30}$/']);
                if (!$validatorAttrCode->isValid($attributeCode)) {
                    $this->messageManager->addError(
                        __(
                            'Attribute code "%1" is invalid. Please use only letters (a-z), ' .
                            'numbers (0-9) or underscore(_) in this field, first character should be a letter.',
                            $attributeCode
                        )
                    );
                    return $resultRedirect->setPath('vendors/*/edit', ['attribute_id' => $attributeId, '_current' => true]);
                }
            }
            $data['attribute_code'] = $attributeCode;

            //validate frontend_input
//             if (isset($data['frontend_input'])) {
//                 /** @var $inputType \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\Validator */
//                 $inputType = $this->validatorFactory->create();
//                 if (!$inputType->isValid($data['frontend_input'])) {
//                     foreach ($inputType->getMessages() as $message) {
//                         $this->messageManager->addError($message);
//                     }
//                     return $resultRedirect->setPath('catalog/*/edit', ['attribute_id' => $attributeId, '_current' => true]);
//                 }
//             }

            if ($attributeId) {
                $model->load($attributeId);
                if (!$model->getId()) {
                    $this->messageManager->addError(__('This attribute no longer exists.'));
                    return $resultRedirect->setPath('vendors/*/');
                }
                // entity type check
                if ($model->getEntityTypeId() != $this->_entityTypeId) {
                    $this->messageManager->addError(__('We can\'t update the attribute.'));
                    $this->_session->setAttributeData($data);
                    return $resultRedirect->setPath('vendors/*/');
                }

                $data['attribute_code'] = $model->getAttributeCode();
                $data['is_user_defined'] = $model->getIsUserDefined();
                $data['frontend_input'] = $model->getFrontendInput();
            } else {
                /**
                 * @todo add to helper and specify all relations for properties
                 */
                switch($data['frontend_input']){
                    case "boolean":
                        $data['source_model'] = 'Magento\Eav\Model\Entity\Attribute\Source\Boolean';
                        break;
                    case "multiselect":
                        $data['source_model'] = 'Magento\Eav\Model\Entity\Attribute\Source\Table';
                        $data['backend_model'] = 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend';
                        break;
                    case "file":
                        $data['backend_model'] = 'Vnecoms\Vendors\Model\Entity\Attribute\Backend\File';
                        break;
                    default:
                        $data['source_model'] = '';
                }
                
                $data['attribute_set_id'] = $attributeSetId;
                $data['attribute_group_id'] = $attributeGroupId;
                $data['used_in_forms'] = ['adminhtml_customer'];
                $data['is_visible_in_customer_form'] = 0;
                $data['attribute_set_id'] = $attributeSetId;
                $data['attribute_group_id'] = $attributeGroupId;
            }
            
            

            if (is_null($model->getIsUserDefined()) || $model->getIsUserDefined() != 0) {
                $data['backend_type'] = $model->getBackendTypeByInput($data['frontend_input']);
            }

            $defaultValueField = $model->getDefaultValueByInput($data['frontend_input']);
            
            if ($defaultValueField) {
                $data['default_value'] = $this->getRequest()->getParam($defaultValueField);
            }

            if (!$model->getIsUserDefined() && $model->getId()) {
                // Unset attribute field for system attributes
                unset($data['apply_to']);
            }

            $model->addData($data);

            if (!$attributeId) {
                $model->setEntityTypeId($this->_entityTypeId);
                $model->setIsUserDefined(1);
            }

            try {
                $model->save();
                $this->messageManager->addSuccess(__('You saved the vendor attribute.'));

                $this->_session->setAttributeData(false);
                if ($redirectBack) {
                    $resultRedirect->setPath('vendors/*/edit', ['attribute_id' => $model->getId(), '_current' => true]);
                } else {
                    $resultRedirect->setPath('vendors/*/');
                }
                return $resultRedirect;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_session->setAttributeData($data);
                return $resultRedirect->setPath('vendors/*/edit', ['attribute_id' => $attributeId, '_current' => true]);
            }
        }
        return $resultRedirect->setPath('vendors/*/');
    }
}
