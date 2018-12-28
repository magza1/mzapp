<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Model\Brand\PostDataProcessor;

use Aheadworks\ShopByBrand\Model\Brand\PostDataProcessor\BrandAttributeId;
use Aheadworks\ShopByBrand\Model\Config;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Aheadworks\ShopByBrand\Model\Brand\PostDataProcessor\BrandAttributeId
 */
class BrandAttributeIdTest extends TestCase
{
    /**
     * @var BrandAttributeId
     */
    private $dataProcessor;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var ProductAttributeRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productAttributeRepositoryMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->configMock = $this->createPartialMock(Config::class, ['getBrandProductAttributeCode']);
        $this->productAttributeRepositoryMock = $this->getMockBuilder(ProductAttributeRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->dataProcessor = $objectManager->getObject(
            BrandAttributeId::class,
            [
                'config' => $this->configMock,
                'productAttributeRepository' => $this->productAttributeRepositoryMock
            ]
        );
    }

    public function testPrepareEntityData()
    {
        $attributeId = 1;
        $attributeCode = 'manufacturer';
        $data = ['brand_id' => null];
        $expectedResult = [
            'brand_id' => null,
            'attribute_id' => $attributeId
        ];

        $attributeMock = $this->getMockBuilder(ProductAttributeInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->configMock->expects($this->once())
            ->method('getBrandProductAttributeCode')
            ->willReturn($attributeCode);
        $this->productAttributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with($attributeCode)
            ->willReturn($attributeMock);
        $attributeMock->expects($this->once())
            ->method('getAttributeId')
            ->willReturn($attributeId);

        $this->assertEquals($expectedResult, $this->dataProcessor->prepareEntityData($data));
    }

    public function testPrepareEntityDataExisting()
    {
        $data = ['brand_id' => 1];
        $this->assertEquals($data, $this->dataProcessor->prepareEntityData($data));
    }
}
