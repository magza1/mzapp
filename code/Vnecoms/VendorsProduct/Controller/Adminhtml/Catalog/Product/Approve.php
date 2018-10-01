<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsProduct\Controller\Adminhtml\Catalog\Product;

use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Controller\Adminhtml\Product\Builder;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class Approve extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * @var \Vnecoms\VendorsProduct\Helper\Data
     */
    protected $productHelper;
    
    /**
     * @var \Vnecoms\Vendors\Model\VendorFactory
     */
    protected $vendorFactory;
    
    /**
     * @param Context $context
     * @param Builder $productBuilder
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Builder $productBuilder,
        \Vnecoms\VendorsProduct\Helper\Data $productHelper,
        \Vnecoms\Vendors\Model\VendorFactory $vendorFactory
    ) {
        $this->productHelper = $productHelper;
        $this->vendorFactory = $vendorFactory;
        
        parent::__construct($context, $productBuilder);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $product = $this->_objectManager->create('Magento\Catalog\Model\Product');
        $product->load($id);
        if (!$product->getId()) {
            $this->messageManager->addError(__('This product no longer exists.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('vendors/catalog_product/');
        }
        
        $updateCollection = $this->_objectManager->create('Vnecoms\VendorsProduct\Model\ResourceModel\Product\Update\Collection');
        $updateCollection->addFieldToFilter('product_id', $product->getId())
            ->addFieldToFilter('status', \Vnecoms\VendorsProduct\Model\Product\Update::STATUS_PENDING);
        foreach ($updateCollection as $update) {
            $tmpProduct = $this->_objectManager->create('Magento\Catalog\Model\Product');
            $tmpProduct->load($id);
            $productData = unserialize($update->getProductData());
            foreach ($productData as $attributeCode => $value) {
                $tmpProduct->setData($attributeCode, $value);
            }
            $update->setStatus(\Vnecoms\VendorsProduct\Model\Product\Update::STATUS_APPROVED)->setId($update->getUpdateId())->save();
            $tmpProduct->setStoreId($update->getStoreId())->save();
        }
        $product->setApproval(\Vnecoms\VendorsProduct\Model\Source\Approval::STATUS_APPROVED)->getResource()->saveAttribute($product, 'approval');
        
        $vendor = $this->_objectManager->create('Vnecoms\Vendors\Model\Vendor')->load($product->getVendorId());
        
        $this->_eventManager->dispatch(
            'vnecoms_vendors_push_notification',
            [
                'vendor_id' => $vendor->getId(),
                'type' => 'product_approval',
                'message' => __('Updates of %1 are approved', '<strong>'.$product->getName().'</strong>'),
                'additional_info' => ['id' => $product->getId()],
            ]
        );
        
        /*Send update product notification email*/
        $this->productHelper->sendUpdateProductApprovedEmailToVendor($product, $vendor, $updateCollection);
        
        $this->messageManager->addSuccess(
            __('The product %1 has been approved', $product->getName())
        );

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('vendors/catalog_product/index');
    }
}
