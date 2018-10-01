<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Model\Brand\Validator;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Model\Brand\Validator\Attribute;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute as EavResource;
use Magento\Eav\Model\Entity\Attribute as EntityAttribute;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\ShopByBrand\Model\Brand\Validator\Attribute
 */
class AttributeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Constants used in the unit tests
     */
    const ATTRIBUTE_ID = 1;
    const ATTRIBUTE_CODE = 'manufacturer';

    /**
     * @var Attribute
     */
    private $validator;

    /**
     * @var EavResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eavResourceMock;

    /**
     * @var AttributeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeFactoryMock;

    /**
     * @var ProductAttributeRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productAttributeRepositoryMock;

    /**
     * @var array
     */
    private $brandData = ['getAttributeId' => self::ATTRIBUTE_ID];

    /**
     * @var array
     */
    private $attributeData = [
        'getAttributeCode' => self::ATTRIBUTE_CODE,
        'getIsVisible' => true,
        'getIsFilterable' => true,
        'getFrontendInput' => 'select'
    ];

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->eavResourceMock = $this->getMock(EavResource::class, ['load'], [], '', false);
        $this->attributeFactoryMock = $this->getMock(AttributeFactory::class, ['create'], [], '', false);
        $this->productAttributeRepositoryMock = $this->getMockForAbstractClass(
            ProductAttributeRepositoryInterface::class
        );
        $this->validator = $objectManager->getObject(
            Attribute::class,
            [
                'eavResource' => $this->eavResourceMock,
                'attributeFactory' => $this->attributeFactoryMock,
                'productAttributeRepository' => $this->productAttributeRepositoryMock
            ]
        );
    }

    /**
     * @param BrandInterface|\PHPUnit_Framework_MockObject_MockObject $brandMock
     * @param EntityAttribute|\PHPUnit_Framework_MockObject_MockObject $attributeMock
     * @param bool $expectedResult
     * @param array $expectedMessages
     * @dataProvider isValidDataProvider
     */
    public function testIsValid($brandMock, $attributeMock, $expectedResult, $expectedMessages)
    {
        $this->attributeFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($attributeMock);
        $this->eavResourceMock->expects($this->any())
            ->method('load')
            ->with($attributeMock, self::ATTRIBUTE_ID);
        $this->productAttributeRepositoryMock->expects($this->any())
            ->method('get')
            ->with(self::ATTRIBUTE_CODE)
            ->willReturn($attributeMock);

        $this->assertEquals($expectedResult, $this->validator->isValid($brandMock));
        $this->assertEquals($expectedMessages, $this->validator->getMessages());
    }

    /**
     * Create brand mock and optionally modify getter result
     *
     * @param string|null $methodModify
     * @param mixed|null $valueModify
     * @return BrandInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createBrandMock($methodModify = null, $valueModify = null)
    {
        /** @var BrandInterface|\PHPUnit_Framework_MockObject_MockObject $brandMock */
        $brandMock = $this->getMockForAbstractClass(BrandInterface::class);
        foreach ($this->brandData as $method => $value) {
            if ($method != $methodModify) {
                $brandMock->expects($this->any())
                    ->method($method)
                    ->willReturn($value);
            } else {
                $brandMock->expects($this->any())
                    ->method($methodModify)
                    ->willReturn($valueModify);
            }
        }
        return $brandMock;
    }

    /**
     * Create attribute mock and optionally modify getter result
     *
     * @param string|null $methodModify
     * @param mixed|null $valueModify
     * @return EntityAttribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createAttributeMock($methodModify = null, $valueModify = null)
    {
        /** @var EntityAttribute|\PHPUnit_Framework_MockObject_MockObject $attributeMock */
        $attributeMock = $this->getMock(
            EntityAttribute::class,
            [
                'getAttributeCode',
                'getIsVisible',
                'getIsFilterable',
                'getFrontendInput'
            ],
            [],
            '',
            false
        );
        foreach ($this->attributeData as $method => $value) {
            if ($method != $methodModify) {
                $attributeMock->expects($this->any())
                    ->method($method)
                    ->willReturn($value);
            } else {
                $attributeMock->expects($this->any())
                    ->method($methodModify)
                    ->willReturn($valueModify);
            }
        }
        return $attributeMock;
    }

    /**
     * @return array
     */
    public function isValidDataProvider()
    {
        return [
            'correct data' => [$this->createBrandMock(), $this->createAttributeMock(), true, []],
            'missing attribute ID' => [
                $this->createBrandMock('getAttributeId', null),
                $this->createAttributeMock(),
                false,
                ['Attribute ID is required.']
            ],
            'invalid attribute' => [
                $this->createBrandMock(),
                $this->createAttributeMock('getIsVisible', false),
                false,
                ['Attribute is invalid.']
            ]
        ];
    }
}
