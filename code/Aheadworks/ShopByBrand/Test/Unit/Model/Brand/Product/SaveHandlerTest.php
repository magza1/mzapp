<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Model\Brand\Product;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Model\Brand\Product\SaveHandler;
use Aheadworks\ShopByBrand\Model\ResourceModel\Product\Collection;
use Aheadworks\ShopByBrand\Api\Data\BrandAdditionalProductsInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Aheadworks\ShopByBrand\Model\Brand\Product\SaveHandler
 */
class SaveHandlerTest extends TestCase
{
    /**
     * @var SaveHandler
     */
    private $saveHandler;

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPoolMock;

    /**
     * @var Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productCollectionMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->resourceConnectionMock = $this->createPartialMock(
            ResourceConnection::class,
            ['getConnectionByName', 'getTableName']
        );
        $this->metadataPoolMock = $this->createPartialMock(MetadataPool::class, ['getMetadata']);
        $this->productCollectionMock = $this->createPartialMock(Collection::class, ['getSelectedProductsPositions']);
        $this->saveHandler = $objectManager->getObject(
            SaveHandler::class,
            [
                'resourceConnection' => $this->resourceConnectionMock,
                'metadataPool' => $this->metadataPoolMock,
                'productCollection' => $this->productCollectionMock
            ]
        );
    }

    /**
     * Set up mocks for getConnection() method
     *
     * @param AdapterInterface|\PHPUnit_Framework_MockObject_MockObject $connectionMock
     */
    private function setUpGetConnection($connectionMock)
    {
        $connectionName = 'default';

        $metadataMock = $this->getMockBuilder(EntityMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->metadataPoolMock->expects($this->once())
            ->method('getMetadata')
            ->with(BrandInterface::class)
            ->willReturn($metadataMock);
        $metadataMock->expects($this->once())
            ->method('getEntityConnectionName')
            ->willReturn($connectionName);
        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnectionByName')
            ->with($connectionName)
            ->willReturn($connectionMock);
    }

    public function testExecute()
    {
        $brandId = 1;
        $tableName = 'aw_sbb_additional_products';
        $productPositionsOrig = [
            1 => '111',
            3 => '100'
        ];
        $productId = 1;
        $productPosition = '123';
        /*$productPositions = [
            1 => '100',
            2 => '101'
        ];*/

        /** @var BrandInterface|\PHPUnit_Framework_MockObject_MockObject $brandMock */
        $brandMock = $this->getMockBuilder(BrandInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $additionalProductsMock = $this->getMockBuilder(BrandAdditionalProductsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $productPositions = [$additionalProductsMock, $additionalProductsMock];

        $brandMock->expects($this->once())
            ->method('getBrandId')
            ->willReturn($brandId);
        $brandMock->expects($this->atLeastOnce())
            ->method('getBrandAdditionalProducts')
            ->willReturn($productPositions);
        $this->productCollectionMock->expects($this->once())
            ->method('getSelectedProductsPositions')
            ->with($brandMock)
            ->willReturn($productPositionsOrig);
        $additionalProductsMock->expects($this->any())
            ->method('getProductId')
            ->willReturn($productId);
        $additionalProductsMock->expects($this->any())
            ->method('getPosition')
            ->willReturn($productPosition);
        $this->setUpGetConnection($connectionMock);
        $this->resourceConnectionMock->expects($this->once())
            ->method('getTableName')
            ->with($tableName)
            ->willReturnSelf();
        $connectionMock->expects($this->any())
            ->method('delete')
            ->withAnyParameters();
        $connectionMock->expects($this->any())
            ->method('insertMultiple')
            ->withAnyParameters();

        $this->assertSame($brandMock, $this->saveHandler->execute($brandMock));
    }
}
