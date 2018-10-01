<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Model\Brand\AttributeCode;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Model\Brand\AttributeCode\ReadHandler;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\ShopByBrand\Model\Brand\AttributeCode\ReadHandler
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
        $attributeId = 1;
        $attributeCode = 'manufacturer';
        $connectionName = 'default';
        $tableName = 'eav_attribute';

        /** @var BrandInterface|\PHPUnit_Framework_MockObject_MockObject $brandMock */
        $brandMock = $this->getMockForAbstractClass(BrandInterface::class);
        $metadataMock = $this->getMockForAbstractClass(EntityMetadataInterface::class);
        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $selectMock = $this->getMock(Select::class, ['from', 'where'], [], '', false);

        $brandMock->expects($this->once())
            ->method('getAttributeId')
            ->willReturn($attributeId);
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
            ->with($tableName, ['attribute_code'])
            ->willReturnSelf();
        $selectMock->expects($this->once())
            ->method('where')
            ->with('attribute_id = ?', $attributeId)
            ->willReturnSelf();
        $connectionMock->expects($this->once())
            ->method('fetchOne')
            ->with($selectMock)
            ->willReturn($attributeCode);
        $brandMock->expects($this->once())
            ->method('setAttributeCode')
            ->with($attributeCode);

        $this->assertSame($brandMock, $this->readHandler->execute($brandMock));
    }
}
