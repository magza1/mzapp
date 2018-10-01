<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Model\Brand\Content;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Api\Data\BrandContentInterface;
use Aheadworks\ShopByBrand\Model\Brand\Content\SaveHandler;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\ShopByBrand\Model\Brand\Content\SaveHandler
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

    public function testExecute()
    {
        $brandId = 1;
        $storeId = 2;
        $metaTitle = 'Brand meta title';
        $metaDescription = 'Brand meta description';
        $description = 'Brand description';
        $connectionName = 'default';
        $tableName = 'aw_sbb_brand_content';

        /** @var BrandInterface|\PHPUnit_Framework_MockObject_MockObject $brandMock */
        $brandMock = $this->getMockForAbstractClass(BrandInterface::class);
        $contentMock = $this->getMockForAbstractClass(BrandContentInterface::class);
        $metadataMock = $this->getMockForAbstractClass(EntityMetadataInterface::class);
        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);

        $brandMock->expects($this->once())
            ->method('getBrandId')
            ->willReturn($brandId);
        $brandMock->expects($this->once())
            ->method('getContent')
            ->willReturn([$contentMock]);
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
        $this->resourceConnectionMock->expects($this->once())
            ->method('getTableName')
            ->with($tableName)
            ->willReturnArgument(0);
        $connectionMock->expects($this->once())
            ->method('delete')
            ->with($tableName, ['brand_id = ?' => $brandId]);
        $contentMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $contentMock->expects($this->once())
            ->method('getMetaTitle')
            ->willReturn($metaTitle);
        $contentMock->expects($this->once())
            ->method('getMetaDescription')
            ->willReturn($metaDescription);
        $contentMock->expects($this->once())
            ->method('getDescription')
            ->willReturn($description);
        $connectionMock->expects($this->once())
            ->method('insertMultiple')
            ->with(
                $tableName,
                [
                    [
                        'brand_id' => $brandId,
                        'store_id' => $storeId,
                        'meta_title' => $metaTitle,
                        'meta_description' => $metaDescription,
                        'description' => $description
                    ]
                ]
            );

        $this->assertSame($brandMock, $this->saveHandler->execute($brandMock));
    }
}
