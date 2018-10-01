<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Model;

use Aheadworks\ShopByBrand\Model\Config;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;

/**
 * Test for \Aheadworks\ShopByBrand\Model\Config
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->config = $objectManager->getObject(
            Config::class,
            ['scopeConfig' => $this->scopeConfigMock]
        );
    }

    public function testGetBrandProductAttributeCode()
    {
        $attributeCode = 'manufacturer';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(Config::XML_PATH_BRAND_PRODUCT_ATTRIBUTE_CODE)
            ->willReturn($attributeCode);
        $this->assertEquals($attributeCode, $this->config->getBrandProductAttributeCode());
    }

    public function testGetProductPageBrandInfoBlockPosition()
    {
        $position = 'before_short_description';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                Config::XML_PATH_DISPLAY_PRODUCT_PAGE_BRAND_INFO,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn($position);
        $this->assertEquals($position, $this->config->getProductPageBrandInfoBlockPosition());
    }

    /**
     * @param bool $value
     * @dataProvider boolDataProvider
     */
    public function testIsDisplayProductPageBrandDescription($value)
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with(
                Config::XML_PATH_DISPLAY_PRODUCT_PAGE_BRAND_DESCRIPTION,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn($value);
        $this->assertSame($value, $this->config->isDisplayProductPageBrandDescription());
    }

    /**
     * @param bool $value
     * @dataProvider boolDataProvider
     */
    public function testIsDisplayMoreFromThisBrandBlock($value)
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with(
                Config::XML_PATH_MFTB_BLOCK_ENABLED,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn($value);
        $this->assertSame($value, $this->config->isDisplayMoreFromThisBrandBlock());
    }

    public function testGetMoreFromThisBrandBlockName()
    {
        $blockName = 'More from this brand';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                Config::XML_PATH_MFTB_BLOCK_NAME,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn($blockName);
        $this->assertEquals($blockName, $this->config->getMoreFromThisBrandBlockName());
    }

    public function testGetMoreFromThisBrandBlockPosition()
    {
        $blockPosition = 'content_top';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                Config::XML_PATH_MFTB_BLOCK_POSITION,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn($blockPosition);
        $this->assertEquals($blockPosition, $this->config->getMoreFromThisBrandBlockPosition());
    }

    public function testGetMoreFromThisBrandBlockLayout()
    {
        $blockLayout = 'single_row';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                Config::XML_PATH_MFTB_BLOCK_LAYOUT,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn($blockLayout);
        $this->assertEquals($blockLayout, $this->config->getMoreFromThisBrandBlockLayout());
    }

    public function testGetMoreFromThisBrandBlockProductsLimit()
    {
        $productsLimit = 10;
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                Config::XML_PATH_MFTB_BLOCK_PRODUCTS_LIMIT,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn($productsLimit);
        $this->assertEquals($productsLimit, $this->config->getMoreFromThisBrandBlockProductsLimit());
    }

    /**
     * @param bool $value
     * @dataProvider boolDataProvider
     */
    public function testMoreFromThisBrandBlockAddToCartEnabled($value)
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with(
                Config::XML_PATH_MFTB_BLOCK_ADD_TO_CART_ENABLED,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn($value);
        $this->assertSame($value, $this->config->isMoreFromThisBrandBlockAddToCartEnabled());
    }

    public function testMoreFromThisBrandSortProductsBy()
    {
        $sortBy = 'bestsellers';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                Config::XML_PATH_MFTB_BLOCK_SORT_PRODUCTS_BY,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn($sortBy);
        $this->assertEquals($sortBy, $this->config->getMoreFromThisBrandSortProductsBy());
    }

    public function testGetBrandUrlSuffix()
    {
        $suffix = '.html';
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn($suffix);
        $this->assertEquals($suffix, $this->config->getBrandUrlSuffix());
    }

    /**
     * @param bool $value
     * @dataProvider boolDataProvider
     */
    public function testIsAddStoreCodeToUrlsEnabled($value)
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with(
                Config::XML_PATH_ADD_STORE_CODE_IN_URL,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT
            )
            ->willReturn($value);
        $this->assertSame($value, $this->config->isAddStoreCodeToUrlsEnabled());
    }

    /**
     * @param bool $value
     * @dataProvider boolDataProvider
     */
    public function testIsAddNoindexToPaginationPages($value)
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with(
                Config::XML_PATH_NOINDEX_PAGINATION_PAGES,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn($value);
        $this->assertSame($value, $this->config->isAddNoindexToPaginationPages());
    }

    /**
     * @return array
     */
    public function boolDataProvider()
    {
        return [[true], [false]];
    }
}
