<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsProduct\Controller\Vendors\Product;

use Magento\Framework\Registry;
use Zend\Stdlib\Parameters;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Vnecoms\VendorsProduct\Model\Source\Approval;

class Save extends \Vnecoms\VendorsProduct\Controller\Vendors\Product
{
    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper
     */
    protected $initializationHelper;

    /**
     * @var \Magento\Catalog\Model\Product\Copier
     */
    protected $productCopier;

    /**
     * @var \Magento\Catalog\Model\Product\TypeTransitionManager
     */
    protected $productTypeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Vnecoms\VendorsProduct\Helper\Data
     */
    protected $vendorProductHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * These attribute will not be checked for approval
     *
     * @var unknown
     */
    protected $notCheckAttributes = [
        'media_gallery',
        'quantity_and_stock_status',
        'image',
        'small_image',
        'thumbnail',
        'affect_product_custom_options',
        'options',
        'shipping_product_rate',
        'product_has_weight'
    ];

    /**
     *
     * @param \Vnecoms\Vendors\App\Action\Context $context
     * @param \Vnecoms\Vendors\App\ConfigInterface $config
     * @param Registry $coreRegistry
     * @param Date $dateFilter
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Initialization\StockDataFilter $stockFilter
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        \Vnecoms\Vendors\App\Action\Context $context,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
        \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $initializationHelper,
        \Magento\Catalog\Model\Product\Copier $productCopier,
        \Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Vnecoms\VendorsProduct\Helper\Data $vendorProductHelper
    ) {
        parent::__construct($context, $productBuilder);
        $this->storeManager         = $storeManager;
        $this->initializationHelper = $initializationHelper;
        $this->productCopier        = $productCopier;
        $this->productTypeManager   = $productTypeManager;
        $this->productRepository    = $productRepository;
        $this->vendorProductHelper  = $vendorProductHelper;
    }


    /**
     * Save product action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $storeId = $this->getRequest()->getParam('store');
        $redirectBack = $this->getRequest()->getParam('back', false);

        $productId = $this->getRequest()->getParam('id');
        $resultRedirect = $this->resultRedirectFactory->create();

        $data = $this->getRequest()->getPostValue();

        /*Unset all values from not allow attributes (if exist)*/
        foreach ($this->vendorProductHelper->getNotUsedVendorAttributes() as $attribute) {
            unset($data['product'][$attribute]);
        }

        $post = new Parameters($data);
        $this->getRequest()->setPost($post);

        $productAttributeSetId = $this->getRequest()->getParam('set');
        $productTypeId = $this->getRequest()->getParam('type');
        if ($data) {
            try {
                $params = $this->getRequest()->getParams();


                $product = $this->productBuilder->build($this->getRequest());
                /*Set vendor ID and save*/
                $product->setVendorId($this->_session->getVendor()->getId());

                $product = $this->initializationHelper->initialize($product);
                $this->productTypeManager->processProduct($product);

                /*Set the curent website id*/
                $product->setWebsiteIds([$this->storeManager->getWebsite()->getId() => $this->storeManager->getWebsite()->getId()]);

                /*Update Approval Attribute*/
                $savedraft = $this->getRequest()->getParam('savedraft', false);

                /*
                 * If this flag is set to false, the product will not be saved
                 * This is used for update approval feature so updated product will not be affacted immediately.
                 * It needs admin to approve to apply the changes.
                */
                $saveProductFlag = true;

                if ($product->getId()) {
                    /*
                     * Update product info
                     * If the product is already pending just do nothing.
                     */
                    if ($this->vendorProductHelper->isUpdateProductsApproval()) {
                        if (!in_array($product->getApproval(), [Approval::STATUS_PENDING, Approval::STATUS_NOT_SUBMITED, Approval::STATUS_UNAPPROVED])) {
                            $changedData = $this->_getChangedData($product);



                            if (sizeof($changedData)) {
                                $saveProductFlag = false;
                                /*Save changed data*/
                                $update = $this->_objectManager->create('Vnecoms\VendorsProduct\Model\Product\Update');

                                /*Check if there is an exist pending update*/
                                $collection = $update->getCollection()
                                    ->addFieldToFilter('vendor_id', $this->_session->getVendor()->getId())
                                    ->addFieldToFilter('store_id', $this->getRequest()->getParam('store', 0))
                                    ->addFieldToFilter('product_id', $product->getId())
                                    ->addFieldToFilter('status', \Vnecoms\VendorsProduct\Model\Product\Update::STATUS_PENDING);
                                if ($collection->count()) {
                                    /*Update changed data*/
                                    $update = $collection->getFirstItem();
                                    $update->setProductData(serialize($changedData));
                                    $update->setId($update->getUpdateId())->save();
                                } else {
                                    $update->setData([
                                        'vendor_id' => $this->_session->getVendor()->getId(),
                                        'store_id' => $this->getRequest()->getParam('store', 0),
                                        'product_id' => $product->getId(),
                                        'product_data' => serialize($changedData),
                                        'status' => \Vnecoms\VendorsProduct\Model\Product\Update::STATUS_PENDING
                                    ])->save();
                                }


                                if (!$savedraft) {
                                    $product->setApproval(Approval::STATUS_PENDING_UPDATE)
                                        ->getResource()
                                        ->saveAttribute($product, 'approval');
                                    $this->vendorProductHelper->sendUpdateProductApprovalEmailToAdmin($product, $this->_getSession()->getVendor());
                                }
                            }
                        } else {
                            if (!$savedraft) {
                                if ($product->getApproval() != Approval::STATUS_PENDING) {
                                    $this->vendorProductHelper->sendUpdateProductApprovalEmailToAdmin($product, $this->_getSession()->getVendor());
                                }

                                $product->setApproval(Approval::STATUS_PENDING)
                                    ->getResource()
                                    ->saveAttribute($product, 'approval');
                            }
                        }
                    } else {
                        if ($product->getApproval() != Approval::STATUS_APPROVED) {
                            $product->setApproval(Approval::STATUS_APPROVED);
                        }
                    }
                } else {

                    /*Add new product*/
                    if (!$this->vendorProductHelper->isNewProductsApproval()) {
                        $product->setApproval(Approval::STATUS_APPROVED);
                    }
                    else{
                        if ($savedraft) {
                            $product->setApproval(Approval::STATUS_NOT_SUBMITED);
                        } elseif ($this->vendorProductHelper->isNewProductsApproval()) {
                            $product->setApproval(Approval::STATUS_PENDING);
                            /*Send new product approval notification email to admin*/
                            $this->vendorProductHelper->sendNewProductApprovalEmailToAdmin($product, $this->_getSession()->getVendor());
                        }
                    }
                }

                if (isset($data['product'][$product->getIdFieldName()])) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Unable to save product'));
                }

                $originalSku = $product->getSku();

                if ($saveProductFlag) {
                    $product->save();
                } else {
                    $tmpProduct = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($product->getId())->setStoreId($this->getRequest()->getParam('store', 0));
                    $productData = $this->getRequest()->getPost('product', []);
                    $changedData = [];
                    foreach ($this->notCheckAttributes as $attributeCode) {
                        if (isset($productData[$attributeCode])) {
                            $changedData[$attributeCode] = $productData[$attributeCode];
                        }
                    }


                    if(!isset($changedData["category_ids"]) && isset($productDat["category_ids"])){
                        $changedData["category_ids"] = $productData["category_ids"];
                    }

                    $tmpProduct = $this->initializationHelper->initializeFromData($tmpProduct, $changedData);

                    //  var_dump($tmpProduct->getData('product_has_weight'));exit;
                    $this->productTypeManager->processProduct($tmpProduct);
                    /*Set vendor ID and save*/
                    $tmpProduct->setVendorId($this->_session->getVendor()->getId());




                    /*Set the curent website id*/
                    $tmpProduct->setWebsiteIds([$this->storeManager->getWebsite()->getId() => $this->storeManager->getWebsite()->getId()]);
                    $tmpProduct->save();
                }

                $this->handleImageRemoveError($data, $product->getId());
                $productId = $product->getId();
                $productAttributeSetId = $product->getAttributeSetId();
                $productTypeId = $product->getTypeId();


                /**
                 * Do copying data to stores
                 */
                if (isset($data['copy_to_stores'])) {
                    foreach ($data['copy_to_stores'] as $storeTo => $storeFrom) {
                        $this->_objectManager->create('Magento\Catalog\Model\Product')
                            ->setStoreId($storeFrom)
                            ->load($productId)
                            ->setStoreId($storeTo)
                            ->save();
                    }
                }

                $this->messageManager->addSuccess(__('You saved the product.'));
                if ($product->getSku() != $originalSku) {
                    $this->messageManager->addNotice(
                        __(
                            'SKU for product %1 has been changed to %2.',
                            $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($product->getName()),
                            $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($product->getSku())
                        )
                    );
                }

                $this->_eventManager->dispatch(
                    'controller_action_catalog_product_save_entity_after',
                    ['controller' => $this]
                );

                if ($redirectBack === 'duplicate') {
                    $newProduct = $this->productCopier->copy($product);
                    $this->messageManager->addSuccess(__('You duplicated the product.'));
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_session->setProductData($data);
                $redirectBack = $productId ? true : 'new';
            } catch (\Exception $e) {
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->messageManager->addError($e->getMessage());
                $this->_session->setProductData($data);
                $redirectBack = $productId ? true : 'new';
            }
        } else {
            $resultRedirect->setPath('catalog/product/index', ['store' => $storeId]);
            $this->messageManager->addError('No data to save');
            return $resultRedirect;
        }

        if ($redirectBack === 'new') {
            $resultRedirect->setPath(
                'catalog/product/new',
                ['set' => $productAttributeSetId, 'type' => $productTypeId]
            );
        } elseif ($redirectBack === 'duplicate' && isset($newProduct)) {
            $resultRedirect->setPath(
                'catalog/product/edit',
                ['id' => $newProduct->getId(), 'back' => null, '_current' => true]
            );
        } elseif ($redirectBack) {
            $resultRedirect->setPath(
                'catalog/product/edit',
                ['id' => $productId, '_current' => true, 'set' => $productAttributeSetId]
            );
        } else {
            $resultRedirect->setPath('catalog/product', ['store' => $storeId]);
        }
        return $resultRedirect;
    }

    /**
     * Notify customer when image was not deleted in specific case.
     * TODO: temporary workaround must be eliminated in MAGETWO-45306
     *
     * @param array $postData
     * @param int $productId
     * @return void
     */
    private function handleImageRemoveError($postData, $productId)
    {
        if (isset($postData['product']['media_gallery']['images'])) {
            $removedImagesAmount = 0;
            foreach ($postData['product']['media_gallery']['images'] as $image) {
                if (!empty($image['removed'])) {
                    $removedImagesAmount++;
                }
            }
            if ($removedImagesAmount) {
                $expectedImagesAmount = count($postData['product']['media_gallery']['images']) - $removedImagesAmount;
                $product = $this->productRepository->getById($productId);
                if ($expectedImagesAmount != count($product->getMediaGallery('images'))) {
                    $this->messageManager->addNotice(
                        __('The image cannot be removed as it has been assigned to the other image role')
                    );
                }
            }
        }
    }

    /**
     * Get changed product data
     *
     * @param \Magento\Catalog\Model\Product $product
     */
    private function _getChangedData(\Magento\Catalog\Model\Product $product)
    {
        $changedData = [];
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $productAttrCollection = $om->create('Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection')
            ->addVisibleFilter();
        $notUsedProductAttr = $this->vendorProductHelper->getNotUsedVendorAttributes();

        foreach ($productAttrCollection as $attribute) {
            $attrCode = $attribute->getAttributeCode();

            if (in_array($attrCode, $notUsedProductAttr)) {
                continue;
            }

            $newData = $product->getData($attrCode);
            if ($attrCode == "tier_price" && is_array($newData)) {
                foreach ($newData as $key => $data) {
                    if (isset($data["delete"]) && $data["delete"] == 1) {
                        unset($newData[$key]);
                    }
                }
            }

            if ($this->compareAttributeValue($attrCode, $newData, $product->getOrigData($attrCode))) {
                $changedData[$attrCode] = $newData;
            }
        }


        return $changedData;
    }

    /**
     * Compare two attribute value
     * return true if they are different.
     *
     * @param unknown $data
     * @param unknown $originData
     */
    private function compareAttributeValue($attrCode, $data, $originData)
    {


        $notCheckAttributes = $this->notCheckAttributes;
        if (is_array($data) &&
            !in_array($attrCode, $notCheckAttributes)
        ) {
            if (!is_array($originData)) {
                $originData = explode(',', $originData);
            }


            if (sizeof($data) <= 0 && sizeof($originData) > 0) {
                $result = true;
            } else {
                $checkMultipleArray = isset($data[0]) && is_array($data[0]);
                // fix array_diff with "tier_price"
                if (!$checkMultipleArray) {
                    $result = sizeof(array_diff($data, $originData)) || sizeof(array_diff($originData, $data));
                } else {
                    if ($attrCode == "tier_price") {
                        $diff = array_diff(array_map('json_encode', $data), array_map('json_encode', $originData));
                        $result = sizeof(array_map('json_decode', $diff));
                    } else {
                        $result = false;
                    }
                }
            }
        } else {
            $result = ($data!== false) && ($data !== null) && ($data != $originData);
        }

        $additionalCompare = false;
        if (in_array($attrCode, $notCheckAttributes)) {
            /*Ignore checking value changes*/
        } else {
            $additionalCompare = true;
        }

        $transport = new \Magento\Framework\DataObject([
            'attribute_code' => $attrCode,
            'new_data' => $data,
            'origin_data' => $originData,
            'compare' => $additionalCompare,
        ]);

        $this->_eventManager->dispatch('vnecoms_vendorsproduct_compare_attribute_value', ['transport' => $transport]);

        $additionalCompare = $transport->getData('compare');

        return $result && $additionalCompare;
    }
}
