<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Vnecoms\VendorsLayerNavigation\Model\Layer\VendorHomePage;

use Magento\Catalog\Model\Layer\ItemCollectionProviderInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use \Vnecoms\VendorsProduct\Model\Source\Approval as ProductApproval;

class ItemCollectionProvider implements ItemCollectionProviderInterface
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $productVisibility;

    /**
     * Catalog config
     *
     * @var \Magento\Catalog\Model\Config
     */
    protected $catalogConfig;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    protected $_productCollection;

    public function __construct
    (
        CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Framework\Registry $registry
    )
    {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->productVisibility = $productVisibility;
        $this->catalogConfig = $catalogConfig;
        $this->_coreRegistry = $registry;
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getCollection(\Magento\Catalog\Model\Category $category)
    {
        return $this->_productCollectionFactory->create();
      //  return $category->getProductCollection();

//        if ($this->_productCollection === null) {
//            $this->_productCollection = $this->_productCollectionFactory->create();
//            $this->_productCollection->addAttributeToSelect($this->catalogConfig->getProductAttributes())
//                ->addAttributeToFilter('vendor_id',$this->getVendor()->getId())
//                ->addAttributeToFilter('approval',ProductApproval::STATUS_APPROVED)
//
//            ->addMinimalPrice()
//            ->addFinalPrice()
//            ->addTaxPercents()
//            ->setVisibility($this->productVisibility->getVisibleInCatalogIds());
//        }
//        return $this->_productCollection;
    }

    public function getVendor()
    {
        return $this->_coreRegistry->registry('vendor');
    }
}
