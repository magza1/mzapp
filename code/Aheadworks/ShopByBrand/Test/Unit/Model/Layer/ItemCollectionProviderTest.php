<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Model\Layer;

use Aheadworks\ShopByBrand\Model\Layer\ItemCollectionProvider;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\ShopByBrand\Model\Layer\ItemCollectionProvider
 */
class ItemCollectionProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ItemCollectionProvider
     */
    private $provider;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactoryMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->collectionFactoryMock = $this->getMock(CollectionFactory::class, ['create'], [], '', false);
        $this->provider = $objectManager->getObject(
            ItemCollectionProvider::class,
            ['collectionFactory' => $this->collectionFactoryMock]
        );
    }

    public function testGetCollection()
    {
        $collectionMock = $this->getMock(Collection::class, [], [], '', false);
        /** @var Category|\PHPUnit_Framework_MockObject_MockObject $categoryMock */
        $categoryMock = $this->getMock(Category::class, [], [], '', false);
        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);
        $this->assertSame($collectionMock, $this->provider->getCollection($categoryMock));
    }
}
