<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Controller\Adminhtml\Brand;

use Aheadworks\ShopByBrand\Controller\Adminhtml\Brand\ProductsGrid;
use Magento\Framework\Controller\Result\Raw;
use Aheadworks\ShopByBrand\Block\Adminhtml\Brand\Products;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Controller\Result\RawFactory;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Aheadworks\ShopByBrand\Controller\Adminhtml\Brand\ProductsGrid
 */
class ProductsGridTest extends TestCase
{
    /**
     * @var ProductsGrid
     */
    private $action;

    /**
     * @var LayoutFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $layoutFactoryMock;

    /**
     * @var RawFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRawFactoryMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->resultRawFactoryMock = $this->createPartialMock(RawFactory::class, ['create']);
        $this->layoutFactoryMock = $this->createPartialMock(LayoutFactory::class, ['create']);
        $this->action = $objectManager->getObject(
            ProductsGrid::class,
            [
                'resultRawFactory' => $this->resultRawFactoryMock,
                'layoutFactory' => $this->layoutFactoryMock
            ]
        );
    }

    public function testExecute()
    {
        $resultHtml = '<html>awtest</html>';
        $resultRawMock = $this->createPartialMock(Raw::class, ['setContents']);
        $layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $productsBlockMock = $this->createPartialMock(Products::class, ['toHtml']);

        $this->resultRawFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRawMock);
        $this->layoutFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($layoutMock);
        $layoutMock->expects($this->once())
            ->method('createBlock')
            ->with(Products::class, 'aw.brand.products.grid')
            ->willReturn($productsBlockMock);
        $productsBlockMock->expects($this->once())
            ->method('toHtml')
            ->willReturn($resultHtml);
        $resultRawMock->expects($this->once())
            ->method('setContents')
            ->with($resultHtml)
            ->willReturnSelf();

        $this->assertSame($resultRawMock, $this->action->execute());
    }
}
