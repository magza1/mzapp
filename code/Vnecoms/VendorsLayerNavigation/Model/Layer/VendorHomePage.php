<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Vnecoms\VendorsLayerNavigation\Model\Layer;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Layer\ContextInterface;
use Magento\Catalog\Model\Layer\StateFactory;
//use Vnecoms\VendorsCategory\Api\CategoryRepositoryInterface as VendorCategoryRepositoryInterface;

class VendorHomePage extends \Magento\Catalog\Model\Layer
{
    protected $vendorCategoryRepository;

    /**
     * VendorHomePage constructor.
     * @param ContextInterface $context
     * @param StateFactory $layerStateFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product $catalogProduct
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $registry
     * @param CategoryRepositoryInterface $categoryRepository
//     * @param VendorCategoryRepositoryInterface $vendorCategoryRepository
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        StateFactory $layerStateFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product $catalogProduct,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        CategoryRepositoryInterface $categoryRepository,
//        VendorCategoryRepositoryInterface $vendorCategoryRepository,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $layerStateFactory,
            $attributeCollectionFactory,
            $catalogProduct,
            $storeManager,
            $registry,
            $categoryRepository,
            $data
        );
//        $this->vendorCategoryRepository = $vendorCategoryRepository;
    }

    /**
     * Retrieve current layer product collection.
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
//    public function getProductCollection()
//    {
//        $collection = $this->collectionProvider->getCollection($this->getCurrentCategory());
//        $this->prepareProductCollection($collection);
//
//        return $collection;
//    }

    /**
     * Get layer state key.
     *
     * @return string
     */
    public function getStateKey()
    {
        $vendor = $this->registry->registry('vendor');

        if (!$this->_stateKey) {
            $this->_stateKey = $this->stateKeyGenerator->toString($vendor);
        }

        return $this->_stateKey;
    }

    /**
     * Retrieve current category model
     * If no category found in registry, the root will be taken
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getCurrentVendorCategory()
    {
        $category = $this->getData('current_vendor_category');
        if ($category === null) {
            $category = $this->registry->registry('current_vendor_category');
            if ($category) {
                $this->setData('current_vendor_category', $category);
            } else {
                $category = $this->getVendorCategoryRepository()->get($this->getRootCategoryId());
                $this->setData('current_vendor_category', $category);
            }
        }

        return $category;
    }

    /**
     * Change current category object
     *
     * @param mixed $category
     * @return \Magento\Catalog\Model\Layer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setCurrentVendorCategory($category)
    {
        if (is_numeric($category)) {
            try {
                $category = $this->getVendorCategoryRepository()->get($category);
            } catch (NoSuchEntityException $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Please correct the category.'), $e);
            }
        } elseif ($category instanceof \Magento\Catalog\Model\Category) {
            if (!$category->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Please correct the category.'));
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Must be category model instance or its id.')
            );
        }

        if ($category->getId() != $this->getCurrentVendorCategory()->getId()) {
            $this->setData('current_vendor_category', $category);
        }

        return $this;
    }

    public function getRootCategoryId()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magento\Framework\Registry')->registry('current_root_cat')->getId();
    }

    public function getVendorCategoryRepository()
    {
        if(!$this->vendorCategoryRepository) {
            $this->vendorCategoryRepository = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Vnecoms\VendorsCategory\Api\CategoryRepositoryInterface');
        }
        return $this->vendorCategoryRepository;
    }
}
