<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Block\Adminhtml\Brand;

use Aheadworks\ShopByBrand\Block\Adminhtml\Brand\Products;
use Magento\Framework\Json\EncoderInterface;
use Aheadworks\ShopByBrand\Model\ResourceModel\Product\Collection;
use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Block\Adminhtml\Brand\AssignProducts;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class AssignProductsTest extends TestCase
{
    /**
     * @var Products
     */
    protected $blockGridMock;

    /**
     * @var AssignProducts
     */
    private $assignProducts;

    /**
     * @var Collection
     */
    private $productCollectionMock;

    /**
     * @var EncoderInterface
     */
    private $jsonEncoderMock;

    /**
     * @var LayoutInterface
     */
    private $layoutMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->productCollectionMock = $this->createPartialMock(
            Collection::class,
            ['getSelectedProductsPositions']
        );
        $this->layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->blockGridMock = $this->createPartialMock(Products::class, ['getBrand']);
        $this->jsonEncoderMock = $this->createPartialMock(EncoderInterface::class, ['encode']);
        $this->assignProducts = $objectManager->getObject(
            AssignProducts::class,
            [
                'jsonEncoder' => $this->jsonEncoderMock,
                'productCollection' => $this->productCollectionMock
            ]
        );
    }

    public function testGetProductsJson()
    {
        $productPositions = [
            1 => '100',
            2 => '101'
        ];
        $productPositionsJson = '{"1":"100", "2":"101"}';
        $brandMock = $this->getMockBuilder(BrandInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->layoutMock->expects($this->once())
            ->method('createBlock')
            ->with(Products::class, 'aw.brand.products.grid')
            ->willReturn($this->blockGridMock);
        $this->blockGridMock->expects($this->any())
            ->method('getBrand')
            ->willReturn($brandMock);
        $this->jsonEncoderMock->expects($this->once())
            ->method('encode')
            ->willReturn($productPositionsJson);
        $this->productCollectionMock->expects($this->once())
            ->method('getSelectedProductsPositions')
            ->with($brandMock)
            ->willReturn($productPositions);

        $this->assignProducts->setLayout($this->layoutMock);
        $this->assertEquals($productPositionsJson, $this->assignProducts->getProductsJson());
    }
}
