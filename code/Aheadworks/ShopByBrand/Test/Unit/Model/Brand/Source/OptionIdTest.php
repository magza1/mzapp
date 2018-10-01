<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Model\Brand\Source;

use Aheadworks\ShopByBrand\Model\Brand\Source\OptionId;
use Aheadworks\ShopByBrand\Model\ResourceModel\BrandAttributeOption as BrandOptionResource;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\ShopByBrand\Model\Brand\Source\OptionId
 */
class OptionIdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OptionId
     */
    private $source;

    /**
     * @var AttributeRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeRepositoryMock;

    /**
     * @var BrandOptionResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $brandOptionResourceMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->attributeRepositoryMock = $this->getMockForAbstractClass(
            AttributeRepositoryInterface::class
        );
        $this->brandOptionResourceMock = $this->getMock(
            BrandOptionResource::class,
            ['getUsedOptionIds'],
            [],
            '',
            false
        );
        $this->source = $objectManager->getObject(
            OptionId::class,
            [
                'attributeRepository' => $this->attributeRepositoryMock,
                'brandOptionResource' => $this->brandOptionResourceMock
            ]
        );
    }

    public function testToOptionArray()
    {
        $attributeCode = 'manufacturer';
        $optionValue = 1;
        $optionLabel = 'Manufacturer';
        $expectedResult = [
            [
                'value' => $optionValue,
                'label' => $optionLabel
            ]
        ];

        $attributeMock = $this->getMockForAbstractClass(
            AbstractAttribute::class,
            [],
            '',
            false,
            false,
            false,
            ['getSource']
        );
        $sourceMock = $this->getMock(Table::class, ['getAllOptions'], [], '', false);

        $class = new \ReflectionClass($this->source);
        $attributeCodeProperty = $class->getProperty('attributeCode');
        $attributeCodeProperty->setAccessible(true);
        $attributeCodeProperty->setValue($this->source, $attributeCode);

        $this->attributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode)
            ->willReturn($attributeMock);
        $attributeMock->expects($this->once())
            ->method('getSource')
            ->willReturn($sourceMock);
        $sourceMock->expects($this->once())
            ->method('getAllOptions')
            ->with(true, true)
            ->willReturn(
                [['value' => $optionValue, 'label' => $optionLabel]]
            );
        $this->brandOptionResourceMock->expects($this->once())
            ->method('getUsedOptionIds')
            ->willReturn([]);

        $this->source->toOptionArray();
        $this->assertEquals($expectedResult, $this->source->toOptionArray());
    }

    public function testToOptionArrayUsedOptionId()
    {
        $attributeCode = 'manufacturer';
        $optionValue = 1;
        $optionLabel = 'Manufacturer';

        $attributeMock = $this->getMockForAbstractClass(
            AbstractAttribute::class,
            [],
            '',
            false,
            false,
            false,
            ['getSource']
        );
        $sourceMock = $this->getMock(Table::class, ['getAllOptions'], [], '', false);

        $class = new \ReflectionClass($this->source);
        $attributeCodeProperty = $class->getProperty('attributeCode');
        $attributeCodeProperty->setAccessible(true);
        $attributeCodeProperty->setValue($this->source, $attributeCode);

        $this->attributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode)
            ->willReturn($attributeMock);
        $attributeMock->expects($this->once())
            ->method('getSource')
            ->willReturn($sourceMock);
        $sourceMock->expects($this->once())
            ->method('getAllOptions')
            ->with(true, true)
            ->willReturn(
                [['value' => $optionValue, 'label' => $optionLabel]]
            );
        $this->brandOptionResourceMock->expects($this->once())
            ->method('getUsedOptionIds')
            ->willReturn([$optionValue]);

        $this->assertEquals([], $this->source->toOptionArray());
    }

    public function testToOptionArrayCurrentOptionId()
    {
        $attributeCode = 'manufacturer';
        $optionValue = 1;
        $optionLabel = 'Manufacturer';
        $expectedResult = [
            [
                'value' => $optionValue,
                'label' => $optionLabel
            ]
        ];

        $attributeMock = $this->getMockForAbstractClass(
            AbstractAttribute::class,
            [],
            '',
            false,
            false,
            false,
            ['getSource']
        );
        $sourceMock = $this->getMock(Table::class, ['getAllOptions'], [], '', false);

        $class = new \ReflectionClass($this->source);
        $attributeCodeProperty = $class->getProperty('attributeCode');
        $attributeCodeProperty->setAccessible(true);
        $attributeCodeProperty->setValue($this->source, $attributeCode);
        $currentOptionIdProperty = $class->getProperty('currentOptionId');
        $currentOptionIdProperty->setAccessible(true);
        $currentOptionIdProperty->setValue($this->source, $optionValue);

        $this->attributeRepositoryMock->expects($this->once())
            ->method('get')
            ->with(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode)
            ->willReturn($attributeMock);
        $attributeMock->expects($this->once())
            ->method('getSource')
            ->willReturn($sourceMock);
        $sourceMock->expects($this->once())
            ->method('getAllOptions')
            ->with(true, true)
            ->willReturn(
                [['value' => $optionValue, 'label' => $optionLabel]]
            );
        $this->brandOptionResourceMock->expects($this->once())
            ->method('getUsedOptionIds')
            ->willReturn([$optionValue]);

        $this->assertEquals($expectedResult, $this->source->toOptionArray());
    }

    public function testToOptionArrayAttributeCodeNotSpecified()
    {
        $this->assertEquals([], $this->source->toOptionArray());
    }

    public function testSetAttributeCode()
    {
        $attributeCode = 'manufacturer';
        $this->assertSame($this->source, $this->source->setAttributeCode($attributeCode));
        $class = new \ReflectionClass($this->source);
        $attributeCodeProperty = $class->getProperty('attributeCode');
        $attributeCodeProperty->setAccessible(true);
        $this->assertEquals($attributeCode, $attributeCodeProperty->getValue($this->source));
    }

    public function testSetCurrentOptionId()
    {
        $optionId = 1;
        $this->assertSame($this->source, $this->source->setCurrentOptionId($optionId));
        $class = new \ReflectionClass($this->source);
        $currentOptionIdProperty = $class->getProperty('currentOptionId');
        $currentOptionIdProperty->setAccessible(true);
        $this->assertEquals($optionId, $currentOptionIdProperty->getValue($this->source));
    }
}
