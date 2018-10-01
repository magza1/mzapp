<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Block\Widget;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Api\Data\BrandSearchResultsInterface;
use Aheadworks\ShopByBrand\Api\BrandRepositoryInterface;
use Aheadworks\ShopByBrand\Block\Widget\ListBrand;
use Aheadworks\ShopByBrand\Model\Brand;
use Aheadworks\ShopByBrand\Model\Config;
use Aheadworks\ShopByBrand\Model\Url;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Test for \Aheadworks\ShopByBrand\Block\Widget\ListBrand
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ListBrandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ListBrand
     */
    private $block;

    /**
     * @var BrandRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $brandRepositoryMock;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var SortOrderBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sortOrderBuilderMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var Url|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->brandRepositoryMock = $this->getMockForAbstractClass(BrandRepositoryInterface::class);
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
        $this->configMock = $this->getMock(Config::class, ['getBrandProductAttributeCode'], [], '', false);
        $this->urlMock = $this->getMock(Url::class, ['getBrandUrl', 'getLogoUrl'], [], '', false);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $context = $objectManager->getObject(
            Context::class,
            ['storeManager' => $this->storeManagerMock]
        );
        $this->block = $objectManager->getObject(
            ListBrand::class,
            [
                'context' => $context,
                'brandRepository' => $this->brandRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'sortOrderBuilder' => $this->sortOrderBuilderMock,
                'config' => $this->configMock,
                'url' => $this->urlMock
            ]
        );
    }

    /**
     * Set up mocks for getSearchResults() method
     *
     * @param BrandSearchResultsInterface|\PHPUnit_Framework_MockObject_MockObject $searchResultsMock
     * @return void
     */
    private function setUpGetSearchResults($searchResultsMock)
    {
        $websiteId = 1;
        $brandAttributeCode = 'manufacturer';

        $sortOrderMock = $this->getMock(SortOrder::class, [], [], '', false);
        $websiteMock = $this->getMockForAbstractClass(WebsiteInterface::class);
        $searchCriteriaMock = $this->getMock(SearchCriteria::class, [], [], '', false);

        $this->sortOrderBuilderMock->expects($this->once())
            ->method('setField')
            ->with(BrandInterface::NAME)
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('setAscendingDirection')
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($sortOrderMock);
        $this->configMock->expects($this->once())
            ->method('getBrandProductAttributeCode')
            ->willReturn($brandAttributeCode);
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willReturn($websiteMock);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn($websiteId);
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addSortOrder')
            ->with($sortOrderMock)
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->exactly(2))
            ->method('addFilter')
            ->withConsecutive(
                [BrandInterface::ATTRIBUTE_CODE, $brandAttributeCode],
                ['website_id', [$websiteId]]
            )
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);
        $this->brandRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($searchResultsMock);
    }

    public function testGetBrands()
    {
        $searchResultsMock = $this->getMockForAbstractClass(BrandSearchResultsInterface::class);
        $brandMock = $this->getMockForAbstractClass(BrandInterface::class);
        $this->setUpGetSearchResults($searchResultsMock);
        $searchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$brandMock]);
        $this->assertEquals([$brandMock], $this->block->getBrands());
    }

    /**
     * @param bool $showName
     * @param bool $result
     * @dataProvider isShowNameDataProvider
     */
    public function testIsShowName($showName, $result)
    {
        $class = new \ReflectionClass($this->block);
        $dataProperty = $class->getProperty('_data');
        $dataProperty->setAccessible(true);
        $dataProperty->setValue($this->block, ['show_name' => $showName]);

        $this->assertEquals($result, $this->block->isShowName());
    }

    public function testGetSearchResults()
    {
        $searchResultsMock = $this->getMockForAbstractClass(BrandSearchResultsInterface::class);
        $this->setUpGetSearchResults($searchResultsMock);

        $class = new \ReflectionClass($this->block);
        $method = $class->getMethod('getSearchResults');
        $method->setAccessible(true);

        $method->invoke($this->block);
        $this->assertSame($searchResultsMock, $method->invoke($this->block));
    }

    /**
     * @param bool $value
     * @dataProvider boolDataProvider
     */
    public function testHasFeaturedBrands($value)
    {
        $searchResultsMock = $this->getMockForAbstractClass(BrandSearchResultsInterface::class);
        $brandMock = $this->getMockForAbstractClass(BrandInterface::class);
        $this->setUpGetSearchResults($searchResultsMock);
        $searchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$brandMock]);
        $brandMock->expects($this->once())
            ->method('getIsFeatured')
            ->willReturn($value);

        $this->assertEquals($value, $this->block->hasFeaturedBrands());
    }

    public function testGetBrandUrl()
    {
        $brandUrl = 'http://localhost/brand/some_brand.html';
        /** @var BrandInterface|\PHPUnit_Framework_MockObject_MockObject $brandMock */
        $brandMock = $this->getMockForAbstractClass(BrandInterface::class);
        $this->urlMock->expects($this->once())
            ->method('getBrandUrl')
            ->with($brandMock)
            ->willReturn($brandUrl);
        $this->assertEquals($brandUrl, $this->block->getBrandUrl($brandMock));
    }

    public function testGetLogoUrl()
    {
        $logo = 'logo.jpg';
        $logoUrl = 'http://localhost/media/aw_sbb/small_image/logo.jpg';

        /** @var BrandInterface|\PHPUnit_Framework_MockObject_MockObject $brandMock */
        $brandMock = $this->getMockForAbstractClass(BrandInterface::class);

        $brandMock->expects($this->once())
            ->method('getLogo')
            ->willReturn($logo);
        $this->urlMock->expects($this->once())
            ->method('getLogoUrl')
            ->with($logo, 'small_image')
            ->willReturn($logoUrl);

        $this->assertEquals($logoUrl, $this->block->getLogoUrl($brandMock));
    }

    /**
     * @return array
     */
    public function isShowNameDataProvider()
    {
        return [[true, true], [false, false], [null, false]];
    }

    /**
     * @return array
     */
    public function boolDataProvider()
    {
        return [[true], [false]];
    }
}
