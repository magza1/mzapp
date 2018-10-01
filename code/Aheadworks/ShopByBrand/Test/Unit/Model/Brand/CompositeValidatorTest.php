<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Model\Brand;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Model\Brand\CompositeValidator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Validator\ValidatorInterface;

/**
 * Test for \Aheadworks\ShopByBrand\Model\Brand\CompositeValidator
 */
class CompositeValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * @param BrandInterface|\PHPUnit_Framework_MockObject_MockObject $brandMock
     * @param ValidatorInterface|\PHPUnit_Framework_MockObject_MockObject $validator1Mock
     * @param ValidatorInterface|\PHPUnit_Framework_MockObject_MockObject $validator2Mock
     * @param bool $expectedResult
     * @param array $expectedMessages
     * @dataProvider isValidDataProvider
     */
    public function testIsValid(
        $brandMock,
        $validator1Mock,
        $validator2Mock,
        $expectedResult,
        $expectedMessages
    ) {
        /** @var CompositeValidator $compositeValidator */
        $compositeValidator = $this->objectManager->getObject(
            CompositeValidator::class,
            ['validators' => [$validator1Mock, $validator2Mock]]
        );

        $this->assertEquals($expectedResult, $compositeValidator->isValid($brandMock));
        $this->assertEquals($expectedMessages, $compositeValidator->getMessages());

    }

    /**
     * Create validator mock
     *
     * @param BrandInterface|\PHPUnit_Framework_MockObject_MockObject $brandMock
     * @param bool $isValidResult
     * @param array $messages
     * @return ValidatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createValidatorMock($brandMock, $isValidResult = true, $messages = [])
    {
        $validatorMock = $this->getMockForAbstractClass(ValidatorInterface::class);
        $validatorMock->expects($this->once())
            ->method('isValid')
            ->with($brandMock)
            ->willReturn($isValidResult);
        $validatorMock->expects($this->any())
            ->method('getMessages')
            ->willReturn($messages);
        return $validatorMock;
    }

    /**
     * @return array
     */
    public function isValidDataProvider()
    {
        $brandMock = $this->getMockForAbstractClass(BrandInterface::class);
        return [
            [
                $brandMock,
                $this->createValidatorMock($brandMock),
                $this->createValidatorMock($brandMock),
                true,
                []
            ],
            [
                $brandMock,
                $this->createValidatorMock($brandMock, false, ['Validation message 1']),
                $this->createValidatorMock($brandMock),
                false,
                ['Validation message 1']
            ],
            [
                $brandMock,
                $this->createValidatorMock($brandMock, false, ['Validation message 1']),
                $this->createValidatorMock($brandMock, false, ['Validation message 2']),
                false,
                ['Validation message 1', 'Validation message 2']
            ]
        ];
    }
}
