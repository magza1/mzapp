<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\ResourceModel;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Api\Data\BrandInterfaceFactory;
use Aheadworks\ShopByBrand\Api\Data\BrandSearchResultsInterface;
use Aheadworks\ShopByBrand\Api\Data\BrandSearchResultsInterfaceFactory;
use Aheadworks\ShopByBrand\Api\BrandRepositoryInterface;
use Aheadworks\ShopByBrand\Model\Config;
use Aheadworks\ShopByBrand\Model\ResourceModel\Brand as BrandResource;
use Aheadworks\ShopByBrand\Model\ResourceModel\Brand\Collection;
use Aheadworks\ShopByBrand\Model\ResourceModel\Brand\CollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class BrandRepository
 * @package Aheadworks\ShopByBrand\Model\ResourceModel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BrandRepository implements BrandRepositoryInterface
{
    /**
     * @var BrandInterface[]
     */
    private $instances = [];

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var BrandInterfaceFactory
     */
    private $brandFactory;

    /**
     * @var BrandResource
     */
    private $brandResource;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var BrandSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param EntityManager $entityManager
     * @param BrandInterfaceFactory $brandFactory
     * @param BrandResource $brandResource
     * @param Config $config
     * @param BrandSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionFactory $collectionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        EntityManager $entityManager,
        BrandInterfaceFactory $brandFactory,
        BrandResource $brandResource,
        Config $config,
        BrandSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory,
        DataObjectHelper $dataObjectHelper,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->entityManager = $entityManager;
        $this->brandFactory = $brandFactory;
        $this->brandResource = $brandResource;
        $this->config = $config;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save(BrandInterface $brand)
    {
        try {
            $this->entityManager->save($brand);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        $brandId = $brand->getBrandId();
        unset($this->instances[$brandId]);
        return $this->get($brandId);
    }

    /**
     * {@inheritdoc}
     */
    public function get($brandId)
    {
        if (!isset($this->instances[$brandId])) {
            /** @var BrandInterface $brand */
            $brand = $this->brandFactory->create();
            $storeId = $this->storeManager->getStore()->getId();
            $arguments = $storeId == Store::DEFAULT_STORE_ID
                ? []
                : ['store_id' => $storeId];
            $this->entityManager->load($brand, $brandId, $arguments);
            if (!$brand->getBrandId()) {
                throw NoSuchEntityException::singleField('brandId', $brandId);
            }
            $this->instances[$brandId] = $brand;
        }
        return $this->instances[$brandId];
    }

    /**
     * {@inheritdoc}
     */
    public function getByProductId($productId)
    {
        $brandId = $this->brandResource->getBrandIdByProductIdAndAttributeCode(
            $productId,
            $this->config->getBrandProductAttributeCode(),
            $this->storeManager->getStore()
        );
        if (!$brandId) {
            throw NoSuchEntityException::singleField('productId', $productId);
        }
        return $this->get($brandId);
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var BrandSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create()
            ->setSearchCriteria($searchCriteria);
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();

        $this->extensionAttributesJoinProcessor->process($collection, BrandInterface::class);
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() == 'website_id') {
                    $collection->addWebsiteFilter($filter->getValue());
                } else {
                    $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                    $fields[] = $filter->getField();
                    $conditions[] = [$condition => $filter->getValue()];
                }
            }
            if ($fields) {
                $collection->addFieldToFilter($fields, $conditions);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        if ($sortOrders = $searchCriteria->getSortOrders()) {
            /** @var \Magento\Framework\Api\SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder($sortOrder->getField(), $sortOrder->getDirection());
            }
        }
        $collection
            ->setCurPage($searchCriteria->getCurrentPage())
            ->setPageSize($searchCriteria->getPageSize());

        $storeId = $this->storeManager->getStore()->getId();
        if ($storeId != Store::DEFAULT_STORE_ID) {
            $collection->setStoreId($storeId);
        }

        $brands = [];
        /** @var \Aheadworks\ShopByBrand\Model\Brand $brandModel */
        foreach ($collection as $brandModel) {
            /** @var BrandInterface $brand */
            $brand = $this->brandFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $brand,
                $brandModel->getData(),
                BrandInterface::class
            );
            $brands[] = $brand;
        }

        return $searchResults->setItems($brands);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(BrandInterface $brand)
    {
        return $this->deleteById($brand->getBrandId());
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($brandId)
    {
        /** @var BrandInterface $brand */
        $brand = $this->brandFactory->create();
        $this->entityManager->load($brand, $brandId);
        if (!$brand->getBrandId()) {
            throw NoSuchEntityException::singleField('brandId', $brandId);
        }
        $this->entityManager->delete($brand);
        unset($this->instances[$brandId]);
        return true;
    }
}
