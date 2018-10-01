<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Model\Brand\PostDataProcessor;

use Aheadworks\ShopByBrand\Model\Brand\PostDataProcessorInterface;
use Aheadworks\ShopByBrand\Model\Brand\PostDataProcessor\Composite;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\ShopByBrand\Model\Brand\PostDataProcessor\Composite
 */
class CompositeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Composite
     */
    private $compositeDataProcessor;

    /**
     * @var PostDataProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataProcessorMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->dataProcessorMock = $this->getMockForAbstractClass(PostDataProcessorInterface::class);
        $this->compositeDataProcessor = $objectManager->getObject(
            Composite::class,
            ['processors' => [$this->dataProcessorMock]]
        );
    }

    public function testPrepareEntityData()
    {
        $data = ['field' => 'value'];
        $this->dataProcessorMock->expects($this->once())
            ->method('prepareEntityData')
            ->with($data)
            ->willReturn($data);
        $this->assertEquals($data, $this->compositeDataProcessor->prepareEntityData($data));
    }
}
