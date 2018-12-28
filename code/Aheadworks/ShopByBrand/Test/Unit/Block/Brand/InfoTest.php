<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Block\Brand;

use Aheadworks\ShopByBrand\Api\BrandRepositoryInterface;
use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Block\Brand\Info;
use Aheadworks\ShopByBrand\Model\Brand;
use Aheadworks\ShopByBrand\Model\Template\FilterProvider;
use Aheadworks\ShopByBrand\Model\Url;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Widget\Model\Template\Filter;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Aheadworks\ShopByBrand\Block\Brand\Info
 */
class UrlTest extends TestCase
{
    /**
     * @var Info
     */
    private $block;

    /**
     * @var BrandRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $brandRepositoryMock;

    /**
     * @var Url|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlMock;

    /**
     * @var FilterProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterProviderMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->brandRepositoryMock = $this->getMockBuilder(BrandRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->urlMock = $this->createPartialMock(Url::class, ['getBrandUrl', 'getLogoUrl']);
        $this->filterProviderMock = $this->createPartialMock(FilterProvider::class, ['getFilter']);
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $context = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
                'storeManager' => $this->storeManagerMock
            ]
        );
        $this->block = $objectManager->getObject(
            Info::class,
            [
                'context' => $context,
                'brandRepository' => $this->brandRepositoryMock,
                'url' => $this->urlMock,
                'filterProvider' => $this->filterProviderMock
            ]
        );
    }

    public function testGetBrand()
    {
        $brandId = 1;

        $brandMock = $this->getMockBuilder(BrandInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('brand_id')
            ->willReturn($brandId);
        $this->brandRepositoryMock->expects($this->once())
            ->method('get')
            ->with($brandId)
            ->willReturn($brandMock);

        $this->block->getBrand();
        $this->assertSame($brandMock, $this->block->getBrand());
    }

    public function testGetBrandUrl()
    {
        $brandId = 1;
        $brandUrl = 'http://localhost/brand/some_brand.html';

        $brandMock = $this->getMockBuilder(BrandInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('brand_id')
            ->willReturn($brandId);
        $this->brandRepositoryMock->expects($this->once())
            ->method('get')
            ->with($brandId)
            ->willReturn($brandMock);
        $this->urlMock->expects($this->once())
            ->method('getBrandUrl')
            ->with($brandMock)
            ->willReturn($brandUrl);

        $this->assertEquals($brandUrl, $this->block->getBrandUrl());
    }

    public function testGetBrandLogoUrl()
    {
        $brandId = 1;
        $logo = 'logo.jpg';
        $imageType = 'small_image';
        $logoUrl = 'http://localhost/media/aw_sbb/small_image/logo.jpg';

        $brandMock = $this->getMockBuilder(BrandInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $class = new \ReflectionClass($this->block);
        $dataProperty = $class->getProperty('_data');
        $dataProperty->setAccessible(true);
        $dataProperty->setValue($this->block, ['image_type' => $imageType]);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('brand_id')
            ->willReturn($brandId);
        $this->brandRepositoryMock->expects($this->once())
            ->method('get')
            ->with($brandId)
            ->willReturn($brandMock);
        $brandMock->expects($this->once())
            ->method('getLogo')
            ->willReturn($logo);
        $this->urlMock->expects($this->once())
            ->method('getLogoUrl')
            ->with($logo, $imageType)
            ->willReturn($logoUrl);

        $this->assertEquals($logoUrl, $this->block->getBrandLogoUrl());
    }

    public function testGetDescriptionHtml()
    {
        $storeId = 1;
        $brandId = 2;
        $brandDescription = '<p>Brand description {{widget type="WidgetClassName"}}</p>';
        $brandDescriptionHtml = '<p>Brand description widget html</p>';

        $filterMock = $this->createPartialMock(Filter::class, ['setStoreId', 'filter']);
        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $brandMock = $this->getMockBuilder(BrandInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->filterProviderMock->expects($this->once())
            ->method('getFilter')
            ->willReturn($filterMock);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('brand_id')
            ->willReturn($brandId);
        $this->brandRepositoryMock->expects($this->once())
            ->method('get')
            ->with($brandId)
            ->willReturn($brandMock);
        $brandMock->expects($this->once())
            ->method('getDescription')
            ->willReturn($brandDescription);
        $filterMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();
        $filterMock->expects($this->once())
            ->method('filter')
            ->with($brandDescription)
            ->willReturn($brandDescriptionHtml);

        $this->assertEquals($brandDescriptionHtml, $this->block->getDescriptionHtml());
    }

    /**
     * @param bool $canShowDescription
     * @param string $description
     * @param bool $result
     * @dataProvider canShowDescriptionDataProvider
     */
    public function testCanShowDescription($canShowDescription, $description, $result)
    {
        $brandId = 1;

        $brandMock = $this->getMockBuilder(BrandInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $class = new \ReflectionClass($this->block);
        $dataProperty = $class->getProperty('_data');
        $dataProperty->setAccessible(true);
        $dataProperty->setValue($this->block, ['can_show_description' => $canShowDescription]);

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with('brand_id')
            ->willReturn($brandId);
        $this->brandRepositoryMock->expects($this->any())
            ->method('get')
            ->with($brandId)
            ->willReturn($brandMock);
        $brandMock->expects($this->any())
            ->method('getDescription')
            ->willReturn($description);

        $this->assertEquals($result, $this->block->canShowDescription());
    }

    /**
     * @return array
     */
    public function canShowDescriptionDataProvider()
    {
        return [
            [true, '<p>Brand description</p>', true],
            [false, '<p>Brand description</p>', false],
            [true, '', false],
            [true, null, false]
        ];
    }
}
