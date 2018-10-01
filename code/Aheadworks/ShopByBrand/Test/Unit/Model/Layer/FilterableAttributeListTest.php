<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Model\Layer;

use Aheadworks\ShopByBrand\Model\Config;
use Aheadworks\ShopByBrand\Model\Layer\FilterableAttributeList;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as ResourceAttribute;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Test for \Aheadworks\ShopByBrand\Model\Layer\FilterableAttributeList
 */
class FilterableAttributeListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FilterableAttributeList
     */
    private $filterableAttributeList;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->collectionFactoryMock = $this->getMock(CollectionFactory::class, ['create'], [], '', false);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->configMock = $this->getMock(Config::class, ['getBrandProductAttributeCode'], [], '', false);
        $this->filterableAttributeList = $objectManager->getObject(
            FilterableAttributeList::class,
            [
                'collectionFactory' => $this->collectionFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'config' => $this->configMock
            ]
        );
    }

    public function testGetList()
    {
        $storeId = 1;
        $brandAttributeCode = 'manufacturer';

        $collectionMock = $this->getMock(
            Collection::class,
            [
                'setItemObjectClass',
                'addStoreLabel',
                'setOrder',
                'addIsFilterableFilter',
                'addFieldToFilter',
                'load'
            ],
            [],
            '',
            false
        );
        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);

        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->configMock->expects($this->once())
            ->method('getBrandProductAttributeCode')
            ->willReturn($brandAttributeCode);
        $collectionMock->expects($this->once())
            ->method('setItemObjectClass')
            ->with(ResourceAttribute::class)
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('addStoreLabel')
            ->with($storeId)
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('setOrder')
            ->with('position', 'ASC')
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('addIsFilterableFilter')
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with('attribute_code', ['neq' => $brandAttributeCode])
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();

        $this->assertSame($collectionMock, $this->filterableAttributeList->getList());
    }
}
