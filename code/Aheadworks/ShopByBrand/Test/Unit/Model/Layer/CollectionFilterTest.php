<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Model\Layer;

use Aheadworks\ShopByBrand\Api\BrandRepositoryInterface;
use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Model\Layer\CollectionFilter;
use Aheadworks\ShopByBrand\Model\ResourceModel\Product\Collection as BrandProductCollection;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Aheadworks\ShopByBrand\Model\Layer\CollectionFilter
 */
class CollectionFilterTest extends TestCase
{
    /**
     * @var CollectionFilter
     */
    private $collectionFilter;

    /**
     * @var CatalogConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $catalogConfigMock;

    /**
     * @var Visibility|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productVisibilityMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var BrandRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $brandRepositoryMock;

    /**
     * @var BrandProductCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $brandProductCollectionMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->catalogConfigMock = $this->createPartialMock(CatalogConfig::class, ['getProductAttributes']);
        $this->productVisibilityMock = $this->createPartialMock(
            Visibility::class,
            ['getVisibleInCatalogIds']
        );
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->brandRepositoryMock = $this->getMockBuilder(BrandRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->brandProductCollectionMock = $this->createPartialMock(
            BrandProductCollection::class,
            [
                'getBrandProductsIds',
                'addAdditionalProducts'
            ]
        );
        $this->collectionFilter = $objectManager->getObject(
            CollectionFilter::class,
            [
                'catalogConfig' => $this->catalogConfigMock,
                'productVisibility' => $this->productVisibilityMock,
                'request' => $this->requestMock,
                'brandRepository' => $this->brandRepositoryMock,
                'productCollection' => $this->brandProductCollectionMock
            ]
        );
    }

    public function testFilter()
    {
        $brandId = 1;
        $attributeCode = 'manufacturer';
        $filteredField = 'entity_id';
        $optionId = 2;
        $productAttributes = ['size', 'color'];
        $visibility = [
            Visibility::VISIBILITY_IN_CATALOG,
            Visibility::VISIBILITY_BOTH
        ];
        $productIds = [1, 2];

        /** @var Collection|\PHPUnit_Framework_MockObject_MockObject $collectionMock */
        $collectionMock = $this->createPartialMock(
            Collection::class,
            [
                'addAttributeToSelect',
                'addMinimalPrice',
                'addFinalPrice',
                'addTaxPercents',
                'addUrlRewrite',
                'addStoreFilter',
                'setVisibility',
                'addFieldToFilter'
            ]
        );
        /** @var Category|\PHPUnit_Framework_MockObject_MockObject $categoryMock */
        $categoryMock = $this->createMock(Category::class);
        $brandMock = $this->getMockBuilder(BrandInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->catalogConfigMock->expects($this->once())
            ->method('getProductAttributes')
            ->willReturn($productAttributes);
        $this->productVisibilityMock->expects($this->once())
            ->method('getVisibleInCatalogIds')
            ->willReturn($visibility);
        $collectionMock->expects($this->once())
            ->method('addAttributeToSelect')
            ->with($productAttributes)
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('addMinimalPrice')
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('addFinalPrice')
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('addTaxPercents')
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('addUrlRewrite')
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('addStoreFilter')
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('setVisibility')
            ->with($visibility)
            ->willReturnSelf();
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('brand_id')
            ->willReturn($brandId);
        $this->brandRepositoryMock->expects($this->once())
            ->method('get')
            ->with($brandId)
            ->willReturn($brandMock);
        $this->brandProductCollectionMock->expects($this->once())
            ->method('getBrandProductsIds')
            ->with($brandMock)
            ->willReturn($productIds);
        $collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with($filteredField, ['in' => $productIds]);

        $this->collectionFilter->filter($collectionMock, $categoryMock);
    }
}
