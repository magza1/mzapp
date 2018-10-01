<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Model\Brand\Validator;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Api\Data\BrandContentInterface;
use Aheadworks\ShopByBrand\Model\Brand\Validator\Content;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\ShopByBrand\Model\Brand\Validator\Content
 */
class ContentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Content
     */
    private $validator;

    /**
     * @var array
     */
    private $contentData = [
        'getStoreId' => 0,
        'getMetaTitle' => 'Meta title',
        'getMetaDescription' => 'Meta descrition',
        'getDescription' => '<p>Description</p>'
    ];

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->validator = $objectManager->getObject(Content::class);
    }

    /**
     * @param BrandContentInterface[]|\PHPUnit_Framework_MockObject_MockObject[] $contentMocks
     * @param bool $expectedResult
     * @param array $expectedMessages
     * @dataProvider isValidDataProvider
     */
    public function testIsValid($contentMocks, $expectedResult, $expectedMessages)
    {
        /** @var BrandInterface|\PHPUnit_Framework_MockObject_MockObject $brandMock */
        $brandMock = $this->getMockForAbstractClass(BrandInterface::class);
        $brandMock->expects($this->any())
            ->method('getContent')
            ->willReturn($contentMocks);

        $this->assertEquals($expectedResult, $this->validator->isValid($brandMock));
        $this->assertEquals($expectedMessages, $this->validator->getMessages());
    }

    /**
     * Create brand content mock and optionally modify getter result
     *
     * @param string|null $methodModify
     * @param mixed|null $valueModify
     * @return BrandContentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createContentMock($methodModify = null, $valueModify = null)
    {
        /** @var BrandContentInterface|\PHPUnit_Framework_MockObject_MockObject $contentMock */
        $contentMock = $this->getMockForAbstractClass(BrandContentInterface::class);
        foreach ($this->contentData as $method => $value) {
            if ($method != $methodModify) {
                $contentMock->expects($this->any())
                    ->method($method)
                    ->willReturn($value);
            } else {
                $contentMock->expects($this->any())
                    ->method($methodModify)
                    ->willReturn($valueModify);
            }
        }
        return $contentMock;
    }

    /**
     * @return array
     */
    public function isValidDataProvider()
    {
        return [
            'correct data' => [[$this->createContentMock()], true, []],
            'duplicated store view' => [
                [$this->createContentMock(), $this->createContentMock()],
                false,
                ['Duplicated store view in content data found.']
            ],
            'missing default values' => [
                [$this->createContentMock('getStoreId', 1)],
                false,
                ['Default values of content data (for All Store Views option) aren\'t set.']
            ],
            'missing meta title' => [
                [$this->createContentMock('getMetaTitle', null)],
                false,
                ['Meta title is required.']
            ],
            'missing meta description' => [
                [$this->createContentMock('getMetaDescription', null)],
                false,
                ['Meta description is required.']
            ],
            'reference the brand itself' => [
                [
                    $this->createContentMock(
                        'getDescription',
                        '{{widget type="Aheadworks\ShopByBrand\Block\Widget\ListBrand"}}'
                    )
                ],
                false,
                ['Make sure that brand description does not reference the brand itself.']
            ]
        ];
    }
}
