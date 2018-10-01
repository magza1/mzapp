<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Model\Brand\Website;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Model\Brand\Website\SaveHandler;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\ShopByBrand\Model\Brand\Website\SaveHandler
 */
class SaveHandlerTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->resourceConnectionMock = $this->getMock(
            ResourceConnection::class,
            ['getConnectionByName', 'getTableName'],
            [],
            '',
            false
        );
        $this->metadataPoolMock = $this->getMock(MetadataPool::class, ['getMetadata'], [], '', false);
        $this->saveHandler = $objectManager->getObject(
            SaveHandler::class,
            [
                'resourceConnection' => $this->resourceConnectionMock,
                'metadataPool' => $this->metadataPoolMock
            ]
        );
    }

    /**
     * Set up mocks for getConnection() method
     *
     * @param AdapterInterface|\PHPUnit_Framework_MockObject_MockObject $connectionMock
     * @param int $calls
     */
    private function setUpGetConnection($connectionMock, $calls = 1)
    {
        $connectionName = 'default';

        $metadataMock = $this->getMockForAbstractClass(EntityMetadataInterface::class);

        $this->metadataPoolMock->expects($this->exactly($calls))
            ->method('getMetadata')
            ->with(BrandInterface::class)
            ->willReturn($metadataMock);
        $metadataMock->expects($this->exactly($calls))
            ->method('getEntityConnectionName')
            ->willReturn($connectionName);
        $this->resourceConnectionMock->expects($this->exactly($calls))
            ->method('getConnectionByName')
            ->with($connectionName)
            ->willReturn($connectionMock);
    }

    public function testExecuteInsert()
    {
        $brandId = 1;
        $websiteId = 2;
        $tableName = 'aw_sbb_brand_website';

        /** @var BrandInterface|\PHPUnit_Framework_MockObject_MockObject $brandMock */
        $brandMock = $this->getMockForAbstractClass(BrandInterface::class);
        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $selectMock = $this->getMock(Select::class, ['from', 'where'], [], '', false);

        $brandMock->expects($this->once())
            ->method('getBrandId')
            ->willReturn($brandId);
        $brandMock->expects($this->once())
            ->method('getWebsiteIds')
            ->willReturn([$websiteId]);
        $this->setUpGetConnection($connectionMock, 2);
        $connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($selectMock);
        $this->resourceConnectionMock->expects($this->exactly(2))
            ->method('getTableName')
            ->with($tableName)
            ->willReturnArgument(0);
        $selectMock->expects($this->once())
            ->method('from')
            ->with($tableName, 'website_id')
            ->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('where')
            ->with('brand_id = :id')
            ->willReturnSelf();
        $connectionMock->expects($this->once())
            ->method('fetchCol')
            ->with($selectMock, ['id' => $brandId])
            ->willReturn([]);
        $connectionMock->expects($this->once())
            ->method('insertMultiple')
            ->with(
                $tableName,
                [
                    ['brand_id' => $brandId, 'website_id' => $websiteId]
                ]
            );

        $this->assertSame($brandMock, $this->saveHandler->execute($brandMock));
    }

    public function testExecuteDelete()
    {
        $brandId = 1;
        $websiteId = 2;
        $tableName = 'aw_sbb_brand_website';

        /** @var BrandInterface|\PHPUnit_Framework_MockObject_MockObject $brandMock */
        $brandMock = $this->getMockForAbstractClass(BrandInterface::class);
        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $selectMock = $this->getMock(Select::class, ['from', 'where'], [], '', false);

        $brandMock->expects($this->once())
            ->method('getBrandId')
            ->willReturn($brandId);
        $brandMock->expects($this->once())
            ->method('getWebsiteIds')
            ->willReturn([]);
        $this->setUpGetConnection($connectionMock, 2);
        $connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($selectMock);
        $this->resourceConnectionMock->expects($this->exactly(2))
            ->method('getTableName')
            ->with($tableName)
            ->willReturnArgument(0);
        $selectMock->expects($this->once())
            ->method('from')
            ->with($tableName, 'website_id')
            ->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('where')
            ->with('brand_id = :id')
            ->willReturnSelf();
        $connectionMock->expects($this->once())
            ->method('fetchCol')
            ->with($selectMock, ['id' => $brandId])
            ->willReturn([$websiteId]);
        $connectionMock->expects($this->once())
            ->method('delete')
            ->with(
                $tableName,
                ['brand_id = ?' => $brandId, 'website_id IN (?)' => [$websiteId]]
            );

        $this->assertSame($brandMock, $this->saveHandler->execute($brandMock));
    }
}
