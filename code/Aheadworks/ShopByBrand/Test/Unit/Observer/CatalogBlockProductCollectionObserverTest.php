<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Observer;

use Aheadworks\ShopByBrand\Observer\CatalogBlockProductCollectionObserver;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Aheadworks\ShopByBrand\Observer\CatalogBlockProductCollectionObserver
 */
class CatalogBlockProductCollectionObserverTest extends TestCase
{
    /**
     * @var CatalogBlockProductCollectionObserver
     */
    private $observer;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->observer = $objectManager->getObject(
            CatalogBlockProductCollectionObserver::class,
            ['request' => $this->requestMock]
        );
    }

    public function testExecute()
    {
        $dir = 'ASC';
        $sort = 'position';
        $fromPart = [
            'additional_brand_products' => [
                'joinType' => 'left join',
                'tableName' => 'aw_sbb_additional_products'
            ]
        ];

        /** @var Observer|\PHPUnit_Framework_MockObject_MockObject $observerMock */
        $observerMock = $this->createPartialMock(Observer::class, ['getEvent']);
        $eventMock = $this->createPartialMock(Event::class, ['__call']);
        $collectionMock = $this->createPartialMock(Collection::class, ['getSelect']);
        $selectMock = $this->createPartialMock(Select::class, ['getPart', 'order']);

        $observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($eventMock);
        $eventMock->expects($this->once())
            ->method('__call')
            ->with('getCollection')
            ->willReturn($collectionMock);
        $collectionMock->expects($this->once())
            ->method('getSelect')
            ->willReturn($selectMock);
        $selectMock->expects($this->once())
            ->method('getPart')
            ->with(Select::FROM)
            ->willReturn($fromPart);
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->withAnyParameters()
            ->willReturn($sort, $dir);

        $this->observer->execute($observerMock);
    }
}
