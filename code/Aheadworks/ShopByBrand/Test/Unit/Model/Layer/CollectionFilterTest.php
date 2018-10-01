<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Model\Layer;

use Aheadworks\ShopByBrand\Api\BrandRepositoryInterface;
use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Model\Layer\CollectionFilter;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\ShopByBrand\Model\Layer\CollectionFilter
 */
class CollectionFilterTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->catalogConfigMock = $this->getMock(CatalogConfig::class, ['getProductAttributes'], [], '', false);
        $this->productVisibilityMock = $this->getMock(
            Visibility::class,
            ['getVisibleInCatalogIds'],
            [],
            '',
            false
        );
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->brandRepositoryMock = $this->getMockForAbstractClass(BrandRepositoryInterface::class);
        $this->collectionFilter = $objectManager->getObject(
            CollectionFilter::class,
            [
                'catalogConfig' => $this->catalogConfigMock,
                'productVisibility' => $this->productVisibilityMock,
                'request' => $this->requestMock,
                'brandRepository' => $this->brandRepositoryMock
            ]
        );
    }

    public function testFilter()
    {
        $brandId = 1;
        $attributeCode = 'manufacturer';
        $optionId = 2;
        $productAttributes = ['size', 'color'];
        $visibility = [
            Visibility::VISIBILITY_IN_CATALOG,
            Visibility::VISIBILITY_BOTH
        ];

        /** @var Collection|\PHPUnit_Framework_MockObject_MockObject $collectionMock */
        $collectionMock = $this->getMock(
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
            ],
            [],
            '',
            false
        );
        /** @var Category|\PHPUnit_Framework_MockObject_MockObject $categoryMock */
        $categoryMock = $this->getMock(Category::class, [], [], '', false);
        $brandMock = $this->getMockForAbstractClass(BrandInterface::class);

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
        $brandMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $brandMock->expects($this->once())
            ->method('getOptionId')
            ->willReturn($optionId);
        $collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with($attributeCode, $optionId);

        $this->collectionFilter->filter($collectionMock, $categoryMock);
    }
}
