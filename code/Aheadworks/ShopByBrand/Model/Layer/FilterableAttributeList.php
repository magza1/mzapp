<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Layer;

use Aheadworks\ShopByBrand\Model\Config;
use Magento\Catalog\Model\Layer\FilterableAttributeListInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as ResourceAttribute;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class FilterableAttributeList
 * @package Aheadworks\ShopByBrand\Model\Layer
 */
class FilterableAttributeList implements FilterableAttributeListInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param CollectionFactory $collectionFactory
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        Config $config
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getList()
    {
        /** @var $collection Collection */
        $collection = $this->collectionFactory->create();
        $collection->setItemObjectClass(ResourceAttribute::class)
            ->addStoreLabel($this->storeManager->getStore()->getId())
            ->setOrder('position', 'ASC')
            ->addIsFilterableFilter()
            ->addFieldToFilter('attribute_code', ['neq' => $this->config->getBrandProductAttributeCode()]);
        $collection->load();

        return $collection;
    }
}
