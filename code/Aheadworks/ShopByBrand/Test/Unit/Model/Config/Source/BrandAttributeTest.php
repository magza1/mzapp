<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
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

/**
 * Test for \Aheadworks\ShopByBrand\Model\Config\Source\BrandAttribute
 */
class BrandAttributeTest extends \PHPUnit_Framework_TestCase
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
        $this->productAttributeRepositoryMock = $this->getMockForAbstractClass(
            ProductAttributeRepositoryInterface::class
        );
        $this->searchCriteriaBuilderMock = $this->getMock(
            SearchCriteriaBuilder::class,
            ['addFilter', 'addSortOrder', 'create'],
            [],
            '',
            false
        );
        $this->sortOrderBuilderMock = $this->getMock(
            SortOrderBuilder::class,
            ['setField', 'setAscendingDirection', 'create'],
            [],
            '',
            false
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
        $sortOrderMock = $this->getMock(SortOrder::class, [], [], '', false);
        $searchCriteriaMock = $this->getMock(SearchCriteria::class, [], [], '', false);
        $searchResultsMock = $this->getMockForAbstractClass(ProductAttributeSearchResultsInterface::class);
        $attributeMock = $this->getMockForAbstractClass(ProductAttributeInterface::class);

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
