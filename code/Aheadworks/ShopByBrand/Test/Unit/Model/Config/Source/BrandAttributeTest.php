<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Model\Config\Source;

use Aheadworks\ShopByBrand\Model\Config\Source\BrandAttribute;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductAttributeSearchResultsInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Aheadworks\ShopByBrand\Model\Config\Source\BrandAttribute
 */
class BrandAttributeTest extends TestCase
{
    /**
     * @var BrandAttribute
     */
    private $source;

    /**
     * @var ProductAttributeRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productAttributeRepositoryMock;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var SortOrderBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sortOrderBuilderMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->productAttributeRepositoryMock = $this->getMockBuilder(ProductAttributeRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilderMock = $this->createPartialMock(
            SearchCriteriaBuilder::class,
            ['addFilter', 'addSortOrder', 'create']
        );
        $this->sortOrderBuilderMock = $this->createPartialMock(
            SortOrderBuilder::class,
            ['setField', 'setAscendingDirection', 'create']
        );
        $this->source = $objectManager->getObject(
            BrandAttribute::class,
            [
                'productAttributeRepository' => $this->productAttributeRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'sortOrderBuilder' => $this->sortOrderBuilderMock
            ]
        );
    }

    public function testGetAttributes()
    {
        $sortOrderMock = $this->createMock(SortOrder::class);
        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $searchResultsMock = $this->getMockBuilder(ProductAttributeSearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $attributeMock = $this->getMockBuilder(ProductAttributeInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->sortOrderBuilderMock->expects($this->once())
            ->method('setField')
            ->with(ProductAttributeInterface::FRONTEND_LABEL)
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('setAscendingDirection')
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($sortOrderMock);

        $this->searchCriteriaBuilderMock->expects($this->exactly(4))
            ->method('addFilter')
            ->withConsecutive(
                [ProductAttributeInterface::IS_VISIBLE, true],
                [ProductAttributeInterface::IS_FILTERABLE, true],
                [ProductAttributeInterface::FRONTEND_INPUT, 'select'],
                [ProductAttributeInterface::BACKEND_TYPE, 'int']
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addSortOrder')
            ->with($sortOrderMock)
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);
        $this->productAttributeRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($searchResultsMock);
        $searchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$attributeMock]);

        $class = new \ReflectionClass($this->source);
        $method = $class->getMethod('getAttributes');
        $method->setAccessible(true);

        $this->assertEquals([$attributeMock], $method->invoke($this->source));
    }
}
