<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Block\Product;

use Aheadworks\ShopByBrand\Api\BrandRepositoryInterface;
use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Block\Product\BrandInfo;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Context;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Aheadworks\ShopByBrand\Block\Product\BrandInfo
 */
class UrlTest extends TestCase
{
    /**
     * @var BrandInfo
     */
    private $block;

    /**
     * @var BrandRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $brandRepositoryMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->brandRepositoryMock = $this->getMockBuilder(BrandRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requestMock = $this->createMock(RequestInterface::class);
        $context = $objectManager->getObject(
            Context::class,
            ['request' => $this->requestMock]
        );
        $this->block = $objectManager->getObject(
            BrandInfo::class,
            [
                'context' => $context,
                'brandRepository' => $this->brandRepositoryMock
            ]
        );
    }

    public function testGetBrand()
    {
        $productId = 1;

        $brandMock = $this->getMockBuilder(BrandInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id')
            ->willReturn($productId);
        $this->brandRepositoryMock->expects($this->once())
            ->method('getByProductId')
            ->with($productId)
            ->willReturn($brandMock);

        $class = new \ReflectionClass($this->block);
        $method = $class->getMethod('getBrand');
        $method->setAccessible(true);

        $this->assertSame($brandMock, $method->invoke($this->block));
    }

    public function testGetBrandNoExist()
    {
        $productId = 1;

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id')
            ->willReturn($productId);
        $this->brandRepositoryMock->expects($this->once())
            ->method('getByProductId')
            ->with($productId)
            ->willThrowException(NoSuchEntityException::singleField('productId', $productId));

        $class = new \ReflectionClass($this->block);
        $method = $class->getMethod('getBrand');
        $method->setAccessible(true);

        $this->assertSame(null, $method->invoke($this->block));
    }
}
