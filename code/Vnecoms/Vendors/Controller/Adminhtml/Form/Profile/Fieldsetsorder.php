<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\Vendors\Controller\Adminhtml\Form\Profile;

use Vnecoms\Vendors\Controller\Adminhtml\Action;

class Fieldsetsorder extends Action
{
    /**
     * Is access to section allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return parent::_isAllowed() && $this->_authorization->isAllowed('Vnecoms_Vendors::vendors_form_profile');
    }
    
    /**
     * @return void
     */
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                $fieldsets = $this->getRequest()->getParam('fieldsets', []);
                
                foreach ($fieldsets as $fieldsetId => $order) {
                    /** @var \Vnecoms\Vendors\Model\Vendor\Fieldset $model */
                    $model = $this->_objectManager->create('Vnecoms\Vendors\Model\Vendor\Fieldset');
                    $model->load($fieldsetId);
                    if (!$model->getId()) {
                        continue;
                    }
                    
                    $model->setSortOrder($order);
                    $model->save();
                }
                //$block = $this->_view->getLayout()->createBlock('Vnecoms\Vendors\Block\Adminhtml\Profile\Form')->setTemplate('Vnecoms_Vendors::profile/container_ajax.phtml');
                $result = ['success'=>true/* ,'form_html'=>$block->toHtml() */];
            } catch (\Exception $e) {
                $result = ['success'=>false,'err_msg'=>__('Something went wrong while saving the form data.<br />'.$e->getMessage())];
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            }
        } else {
            $result = ['success'=>false,'err_msg'=>__('The post data is not valid.')];
        }
        
        $this->getResponse()->setBody(json_encode($result));
    }
}
