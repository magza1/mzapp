<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Model\Brand\PostDataProcessor;

use Aheadworks\ShopByBrand\Model\Brand\PostDataProcessor\ContentData;
use Magento\Framework\Stdlib\BooleanUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Aheadworks\ShopByBrand\Model\Brand\PostDataProcessor\ContentData
 */
class ContentDataTest extends TestCase
{
    /**
     * @var ContentData
     */
    private $dataProcessor;

    /**
     * @var BooleanUtils|\PHPUnit_Framework_MockObject_MockObject
     */
    private $booleanUtilsMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->booleanUtilsMock = $this->createPartialMock(BooleanUtils::class, ['toBoolean']);
        $this->dataProcessor = $objectManager->getObject(
            ContentData::class,
            ['booleanUtils' => $this->booleanUtilsMock]
        );
    }

    public function testPrepareEntityData()
    {
        $data = [
            'content' => [
                [
                    'meta_title' => 'Meta title',
                    'removed' => '0'
                ],
                [
                    'meta_title' => 'Meta title removed',
                    'removed' => '1'
                ]
            ]
        ];
        $expectedResult = [
            'content' => [
                [
                    'meta_title' => 'Meta title',
                    'removed' => '0'
                ]
            ]
        ];

        $this->booleanUtilsMock->expects($this->exactly(2))
            ->method('toBoolean')
            ->withConsecutive(['0'], ['1'])
            ->willReturnOnConsecutiveCalls(false, true);

        $this->assertEquals($expectedResult, $this->dataProcessor->prepareEntityData($data));
    }
}
