<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Model\Layer;

use Aheadworks\ShopByBrand\Model\Layer\ItemCollectionProvider;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Aheadworks\ShopByBrand\Model\Layer\ItemCollectionProvider
 */
class ItemCollectionProviderTest extends TestCase
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
        $this->collectionFactoryMock = $this->createPartialMock(CollectionFactory::class, ['create']);
        $this->provider = $objectManager->getObject(
            ItemCollectionProvider::class,
            ['collectionFactory' => $this->collectionFactoryMock]
        );
    }

    public function testGetCollection()
    {
        $collectionMock = $this->createMock(Collection::class);
        /** @var Category|\PHPUnit_Framework_MockObject_MockObject $categoryMock */
        $categoryMock = $this->createMock(Category::class);
        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);
        $this->assertSame($collectionMock, $this->provider->getCollection($categoryMock));
    }
}
