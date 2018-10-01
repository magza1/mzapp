<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Model\Brand\Website;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Model\Brand\Website\ReadHandler;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\ShopByBrand\Model\Brand\Website\ReadHandler
 */
class ReadHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReadHandler
     */
    private $readHandler;

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
        $this->readHandler = $objectManager->getObject(
            ReadHandler::class,
            [
                'resourceConnection' => $this->resourceConnectionMock,
                'metadataPool' => $this->metadataPoolMock
            ]
        );
    }

    public function testExecute()
    {
        $brandId = 1;
        $websiteId = 2;
        $connectionName = 'default';
        $tableName = 'aw_sbb_brand_website';

        /** @var BrandInterface|\PHPUnit_Framework_MockObject_MockObject $brandMock */
        $brandMock = $this->getMockForAbstractClass(BrandInterface::class);
        $metadataMock = $this->getMockForAbstractClass(EntityMetadataInterface::class);
        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $selectMock = $this->getMock(Select::class, ['from', 'where'], [], '', false);

        $brandMock->expects($this->once())
            ->method('getBrandId')
            ->willReturn($brandId);
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
        $connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($selectMock);
        $this->resourceConnectionMock->expects($this->once())
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
        $brandMock->expects($this->once())
            ->method('setWebsiteIds')
            ->with([$websiteId]);

        $this->assertSame($brandMock, $this->readHandler->execute($brandMock));
    }
}
