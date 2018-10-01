<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Model\Brand\Validator;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Model\Brand\Validator\UrlKey;
use Aheadworks\ShopByBrand\Model\ResourceModel\Validator\UrlKeyIsUnique;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\ShopByBrand\Model\Brand\Validator\UrlKey
 */
class UrlKeyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UrlKey
     */
    private $validator;

    /**
     * @var UrlKeyIsUnique|\PHPUnit_Framework_MockObject_MockObject
     */
    private $uniquenessValidatorMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->uniquenessValidatorMock = $this->getMock(UrlKeyIsUnique::class, ['validate'], [], '', false);
        $this->validator = $objectManager->getObject(
            UrlKey::class,
            ['uniquenessValidator' => $this->uniquenessValidatorMock]
        );
    }

    /**
     * @param string $urlKey
     * @param bool $expectedResult
     * @param array $expectedMessages
     * @dataProvider isValidDataProvider
     */
    public function testIsValid($urlKey, $expectedResult, $expectedMessages)
    {
        /** @var BrandInterface|\PHPUnit_Framework_MockObject_MockObject $brandMock */
        $brandMock = $this->getMockForAbstractClass(BrandInterface::class);

        $brandMock->expects($this->any())
            ->method('getUrlKey')
            ->willReturn($urlKey);
        $this->uniquenessValidatorMock->expects($this->any())
            ->method('validate')
            ->willReturn(true);

        $this->assertEquals($expectedResult, $this->validator->isValid($brandMock));
        $this->assertEquals($expectedMessages, $this->validator->getMessages());
    }

    public function testIsValidDuplicate()
    {
        $urlKey = 'brand';

        /** @var BrandInterface|\PHPUnit_Framework_MockObject_MockObject $brandMock */
        $brandMock = $this->getMockForAbstractClass(BrandInterface::class);

        $brandMock->expects($this->any())
            ->method('getUrlKey')
            ->willReturn($urlKey);
        $this->uniquenessValidatorMock->expects($this->any())
            ->method('validate')
            ->willReturn(false);

        $this->assertEquals(false, $this->validator->isValid($brandMock));
        $this->assertEquals(
            ['This URL-Key is already assigned to another brand.'],
            $this->validator->getMessages()
        );
    }

    /**
     * @return array
     */
    public function isValidDataProvider()
    {
        return [
            'correct data' => ['brand', true, []],
            'missing url key' => [null, false, ['Url key is required.']],
            'numeric url key' => ['123', false, ['Url key consists of numbers.']],
            'url with disallowed symbols' => ['invalid key*^', false, ['Url key contains disallowed symbols.']]
        ];
    }
}
