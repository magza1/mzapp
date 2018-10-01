<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Model\Brand\Source;

use Aheadworks\ShopByBrand\Model\Brand\Source\WebsiteId;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ResourceModel\Website\Collection;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory;

/**
 * Test for \Aheadworks\ShopByBrand\Model\Brand\Source\WebsiteId
 */
class WebsiteIdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WebsiteId
     */
    private $source;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $websiteCollectionFactoryMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->websiteCollectionFactoryMock = $this->getMock(CollectionFactory::class, ['create'], [], '', false);
        $this->source = $objectManager->getObject(
            WebsiteId::class,
            ['websiteCollectionFactory' => $this->websiteCollectionFactoryMock]
        );
    }

    public function testToOptionArray()
    {
        $options = [['value' => 1, 'label' => 'Website 1']];

        $collectionMock = $this->getMock(Collection::class, ['toOptionArray'], [], '', false);

        $this->websiteCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);
        $collectionMock->expects($this->once())
            ->method('toOptionArray')
            ->willReturn($options);

        $this->source->toOptionArray();
        $this->assertEquals($options, $this->source->toOptionArray());
    }
}
