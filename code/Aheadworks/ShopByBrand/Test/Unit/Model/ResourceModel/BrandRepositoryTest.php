<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Model\ResourceModel;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Api\Data\BrandInterfaceFactory;
use Aheadworks\ShopByBrand\Api\Data\BrandSearchResultsInterface;
use Aheadworks\ShopByBrand\Api\Data\BrandSearchResultsInterfaceFactory;
use Aheadworks\ShopByBrand\Model\Brand;
use Aheadworks\ShopByBrand\Model\Config;
use Aheadworks\ShopByBrand\Model\ResourceModel\Brand as BrandResource;
use Aheadworks\ShopByBrand\Model\ResourceModel\Brand\Collection;
use Aheadworks\ShopByBrand\Model\ResourceModel\Brand\CollectionFactory;
use Aheadworks\ShopByBrand\Model\ResourceModel\BrandRepository;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Aheadworks\ShopByBrand\Model\ResourceModel\BrandRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BrandRepositoryTest extends TestCase
{
    /**
     * @var BrandRepository
     */
    private $repository;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var BrandInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $brandFactoryMock;

    /**
     * @var BrandResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $brandResourceMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var BrandSearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultsFactoryMock;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var JoinProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributesJoinProcessorMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->entityManagerMock = $this->createPartialMock(
            EntityManager::class,
            ['save', 'load', 'delete']
        );
        $this->brandFactoryMock = $this->createPartialMock(BrandInterfaceFactory::class, ['create']);
        $this->brandResourceMock = $this->createPartialMock(
            BrandResource::class,
            ['getBrandIdByProductIdAndAttributeCode']
        );
        $this->configMock = $this->createPartialMock(Config::class, ['getBrandProductAttributeCode']);
        $this->searchResultsFactoryMock = $this->createPartialMock(
            BrandSearchResultsInterfaceFactory::class,
            ['create']
        );
        $this->collectionFactoryMock = $this->createPartialMock(CollectionFactory::class, ['create']);
        $this->dataObjectHelperMock = $this->createPartialMock(DataObjectHelper::class, ['populateWithArray']);
        $this->extensionAttributesJoinProcessorMock = $this->getMockBuilder(JoinProcessorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->repository = $objectManager->getObject(
            BrandRepository::class,
            [
                'entityManager' => $this->entityManagerMock,
                'brandFactory' => $this->brandFactoryMock,
                'brandResource' => $this->brandResourceMock,
                'config' => $this->configMock,
                'searchResultsFactory' => $this->searchResultsFactoryMock,
                'collectionFactory' => $this->collectionFactoryMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'extensionAttributesJoinProcessor' => $this->extensionAttributesJoinProcessorMock,
                'storeManager' => $this->storeManagerMock
            ]
        );
    }

    public function testSave()
    {
        $brandId = 1;

        /** @var BrandInterface|\PHPUnit_Framework_MockObject_MockObject $brandMock */
        $brandMock = $this->getMockBuilder(BrandInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($brandMock);
        $brandMock->expects($this->exactly(2))
            ->method('getBrandId')
            ->willReturn($brandId);
        $this->brandFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($brandMock);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn(Store::DEFAULT_STORE_ID);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($brandMock, $brandId, []);

        $this->assertSame($brandMock, $this->repository->save($brandMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Exception message
     */
    public function testSaveException()
    {
        $exceptionMessage = 'Exception message';

        /** @var BrandInterface|\PHPUnit_Framework_MockObject_MockObject $brandMock */
        $brandMock = $this->getMockBuilder(BrandInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($brandMock)
            ->willThrowException(new \Exception($exceptionMessage));

        $this->repository->save($brandMock);
    }

    public function testGet()
    {
        $brandId = 1;

        /** @var BrandInterface|\PHPUnit_Framework_MockObject_MockObject $brandMock */
        $brandMock = $this->getMockBuilder(BrandInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->brandFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($brandMock);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn(Store::DEFAULT_STORE_ID);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($brandMock, $brandId, []);
        $brandMock->expects($this->once())
            ->method('getBrandId')
            ->willReturn($brandId);

        $this->repository->get($brandId);
        $this->assertSame($brandMock, $this->repository->get($brandId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with brandId = 1
     */
    public function testGetException()
    {
        $brandId = 1;

        /** @var BrandInterface|\PHPUnit_Framework_MockObject_MockObject $brandMock */
        $brandMock = $this->getMockBuilder(BrandInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->brandFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($brandMock);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn(Store::DEFAULT_STORE_ID);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($brandMock, $brandId, []);
        $brandMock->expects($this->once())
            ->method('getBrandId')
            ->willReturn(null);

        $this->repository->get($brandId);
    }

    public function testGetByProductId()
    {
        $productId = 1;
        $brandId = 2;
        $attributeCode = 'manufacturer';

        /** @var BrandInterface|\PHPUnit_Framework_MockObject_MockObject $brandMock */
        $brandMock = $this->getMockBuilder(BrandInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->configMock->expects($this->once())
            ->method('getBrandProductAttributeCode')
            ->willReturn($attributeCode);
        $this->storeManagerMock->expects($this->exactly(2))
            ->method('getStore')
            ->willReturn($storeMock);
        $this->brandResourceMock->expects($this->once())
            ->method('getBrandIdByProductIdAndAttributeCode')
            ->with($productId, $attributeCode, $storeMock)
            ->willReturn($brandId);
        $this->brandFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($brandMock);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn(Store::DEFAULT_STORE_ID);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($brandMock, $brandId, []);
        $brandMock->expects($this->once())
            ->method('getBrandId')
            ->willReturn($brandId);

        $this->assertSame($brandMock, $this->repository->getByProductId($productId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with productId = 1
     */
    public function testGetByProductIdException()
    {
        $productId = 1;
        $attributeCode = 'manufacturer';

        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->configMock->expects($this->once())
            ->method('getBrandProductAttributeCode')
            ->willReturn($attributeCode);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $this->brandResourceMock->expects($this->once())
            ->method('getBrandIdByProductIdAndAttributeCode')
            ->with($productId, $attributeCode, $storeMock)
            ->willReturn(null);

        $this->repository->getByProductId($productId);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetList()
    {
        $filterField = 'attribute_id';
        $filterValue = 1;
        $sortOrderField = 'brand_id';
        $sortOrderDirection = 'ASC';
        $size = 10;
        $currPage = 1;
        $pageSize = 5;
        $storeId = 1;
        $itemData = ['brand_id' => 2];
        /** @var SearchCriteriaInterface|\PHPUnit_Framework_MockObject_MockObject $searchCriteriaMock */
        $searchCriteriaMock = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $searchResultsMock = $this->getMockBuilder(BrandSearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $collectionMock = $this->createPartialMock(
            Collection::class,
            [
                'addFieldToFilter',
                'getSize',
                'addOrder',
                'setCurPage',
                'setPageSize',
                'setStoreId',
                'getIterator'
            ]
        );
        $filterGroupMock = $this->createPartialMock(FilterGroup::class, ['getFilters']);
        $filterMock = $this->createPartialMock(
            Filter::class,
            ['getField', 'getConditionType', 'getValue']
        );
        $sortOrderMock = $this->createPartialMock(SortOrder::class, ['getField', 'getDirection']);
        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $brandModelMock = $this->createPartialMock(Brand::class, ['getData']);
        $brandMock = $this->getMockBuilder(BrandInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->searchResultsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchResultsMock);
        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);
        $this->extensionAttributesJoinProcessorMock->expects($this->once())
            ->method('process')
            ->with($collectionMock, BrandInterface::class);
        $searchCriteriaMock->expects($this->once())
            ->method('getFilterGroups')
            ->willReturn([$filterGroupMock]);
        $filterGroupMock->expects($this->once())
            ->method('getFilters')
            ->willReturn([$filterMock]);
        $filterMock->expects($this->exactly(2))
            ->method('getField')
            ->willReturn($filterField);
        $filterMock->expects($this->once())
            ->method('getValue')
            ->willReturn($filterValue);
        $filterMock->expects($this->exactly(2))
            ->method('getConditionType')
            ->willReturn('eq');
        $collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with([$filterField], [['eq' => $filterValue]]);
        $collectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn($size);
        $searchResultsMock->expects($this->once())
            ->method('setTotalCount')
            ->with($size);
        $searchCriteriaMock->expects($this->once())
            ->method('getSortOrders')
            ->willReturn([$sortOrderMock]);
        $sortOrderMock->expects($this->once())
            ->method('getField')
            ->willReturn($sortOrderField);
        $sortOrderMock->expects($this->once())
            ->method('getDirection')
            ->willReturn($sortOrderDirection);
        $collectionMock->expects($this->once())
            ->method('addOrder')
            ->with($sortOrderField, $sortOrderDirection);
        $searchCriteriaMock->expects($this->once())
            ->method('getCurrentPage')
            ->willReturn($currPage);
        $collectionMock->expects($this->once())
            ->method('setCurPage')
            ->with($currPage)
            ->willReturnSelf();
        $searchCriteriaMock->expects($this->once())
            ->method('getPageSize')
            ->willReturn($pageSize);
        $collectionMock->expects($this->once())
            ->method('setPageSize')
            ->with($pageSize)
            ->willReturnSelf();
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $collectionMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId);
        $collectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$brandModelMock]));
        $this->brandFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($brandMock);
        $brandModelMock->expects($this->once())
            ->method('getData')
            ->willReturn($itemData);
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with(
                $brandMock,
                $itemData,
                BrandInterface::class
            );
        $searchResultsMock->expects($this->once())
            ->method('setSearchCriteria')
            ->with($searchCriteriaMock)
            ->willReturnSelf();
        $searchResultsMock->expects($this->once())
            ->method('setItems')
            ->with([$brandMock])
            ->willReturnSelf();

        $this->assertEquals($searchResultsMock, $this->repository->getList($searchCriteriaMock));
    }

    public function testDelete()
    {
        $brandId = 1;

        /** @var BrandInterface|\PHPUnit_Framework_MockObject_MockObject $brandMock */
        $brandMock = $this->getMockBuilder(BrandInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $brandMock->expects($this->exactly(2))
            ->method('getBrandId')
            ->willReturn($brandId);
        $this->brandFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($brandMock);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($brandMock, $brandId);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($brandMock);

        $this->assertEquals(true, $this->repository->delete($brandMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with brandId = 1
     */
    public function testDeleteException()
    {
        $brandId = 1;

        /** @var BrandInterface|\PHPUnit_Framework_MockObject_MockObject $brandMock */
        $brandMock = $this->getMockBuilder(BrandInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $brandMock->expects($this->exactly(2))
            ->method('getBrandId')
            ->willReturnOnConsecutiveCalls($brandId, null);
        $this->brandFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($brandMock);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($brandMock, $brandId);

        $this->repository->delete($brandMock);
    }

    public function testDeleteById()
    {
        $brandId = 1;

        /** @var BrandInterface|\PHPUnit_Framework_MockObject_MockObject $brandMock */
        $brandMock = $this->getMockBuilder(BrandInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->brandFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($brandMock);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($brandMock, $brandId);
        $brandMock->expects($this->once())
            ->method('getBrandId')
            ->willReturn($brandId);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($brandMock);

        $this->assertEquals(true, $this->repository->deleteById($brandId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with brandId = 1
     */
    public function testDeleteByIdException()
    {
        $brandId = 1;

        /** @var BrandInterface|\PHPUnit_Framework_MockObject_MockObject $brandMock */
        $brandMock = $this->getMockBuilder(BrandInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->brandFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($brandMock);
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($brandMock, $brandId);
        $brandMock->expects($this->once())
            ->method('getBrandId')
            ->willReturn(null);

        $this->repository->deleteById($brandId);
    }
}
