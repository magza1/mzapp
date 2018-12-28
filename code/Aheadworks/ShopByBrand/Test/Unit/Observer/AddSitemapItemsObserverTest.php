<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Observer;

use Aheadworks\ShopByBrand\Model\Sitemap;
use Aheadworks\ShopByBrand\Model\Sitemap\ItemsProvider;
use Aheadworks\ShopByBrand\Observer\AddSitemapItemsObserver;
use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Aheadworks\ShopByBrand\Observer\AddSitemapItemsObserver
 */
class AddSitemapItemsObserverTest extends TestCase
{
    /**
     * @var AddSitemapItemsObserver
     */
    private $observer;

    /**
     * @var ItemsProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemsProviderMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->itemsProviderMock = $this->createPartialMock(ItemsProvider::class, ['getBrandItems']);
        $this->observer = $objectManager->getObject(
            AddSitemapItemsObserver::class,
            ['itemsProvider' => $this->itemsProviderMock]
        );
    }

    public function testExecute()
    {
        $storeId = 1;

        /** @var Observer|\PHPUnit_Framework_MockObject_MockObject $observerMock */
        $observerMock = $this->createPartialMock(Observer::class, ['getEvent']);
        $eventMock = $this->createPartialMock(Event::class, ['__call']);
        $sitemapMock = $this->createPartialMock(Sitemap::class, ['appendSitemapItem', '__call']);
        $itemsMock = $this->createMock(DataObject::class);

        $observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($eventMock);
        $eventMock->expects($this->once())
            ->method('__call')
            ->with('getObject')
            ->willReturn($sitemapMock);
        $sitemapMock->expects($this->once())
            ->method('__call')
            ->with('getStoreId')
            ->willReturn($storeId);
        $this->itemsProviderMock->expects($this->once())
            ->method('getBrandItems')
            ->with($storeId)
            ->willReturn($itemsMock);
        $sitemapMock->expects($this->once())
            ->method('appendSitemapItem')
            ->with($itemsMock);

        $this->observer->execute($observerMock);
    }
}
