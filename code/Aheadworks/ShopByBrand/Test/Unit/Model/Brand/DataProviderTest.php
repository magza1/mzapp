<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Model\Brand;

use Aheadworks\ShopByBrand\Model\Brand\DataProvider;
use Aheadworks\ShopByBrand\Model\ResourceModel\Brand\Collection;
use Aheadworks\ShopByBrand\Model\ResourceModel\Brand\CollectionFactory;
use Aheadworks\ShopByBrand\Model\Brand;
use Aheadworks\ShopByBrand\Model\Brand\FileInfo;
use Aheadworks\ShopByBrand\Model\Url;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Aheadworks\ShopByBrand\Model\Brand\DataProvider
 */
class DataProviderTest extends TestCase
{
    /**
     * @var DataProvider
     */
    private $dataProvider;

    /**
     * @var Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionMock;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var DataPersistorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataPersistorMock;

    /**
     * @var FileInfo|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileInfoMock;

    /**
     * @var Url|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->collectionMock = $this->createPartialMock(
            Collection::class,
            ['getItems', 'getNewEmptyItem']
        );
        $this->collectionFactoryMock = $this->createPartialMock(CollectionFactory::class, ['create']);
        $this->dataPersistorMock = $this->getMockBuilder(DataPersistorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->fileInfoMock = $this->createPartialMock(
            FileInfo::class,
            ['isExist', 'getStat', 'getMimeType']
        );
        $this->urlMock = $this->createPartialMock(Url::class, ['getLogoUrl']);

        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->collectionMock);

        $this->dataProvider = $objectManager->getObject(
            DataProvider::class,
            [
                'brandCollectionFactory' => $this->collectionFactoryMock,
                'dataPersistor' => $this->dataPersistorMock,
                'fileInfo' => $this->fileInfoMock,
                'url' => $this->urlMock
            ]
        );
    }

    public function testGetDataEmpty()
    {
        $this->collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([]);
        $this->dataPersistorMock->expects($this->once())
            ->method('get')
            ->with('aw_brand')
            ->willReturn(null);
        $this->assertNull($this->dataProvider->getData());
    }

    public function testGetDataExisting()
    {
        $brandId = 1;
        $brandData = [
            'brand_id' => $brandId,
            'attribute_id' => 2
        ];

        $brandMock = $this->createPartialMock(Brand::class, ['getBrandId', 'getData']);

        $this->collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$brandMock]);
        $brandMock->expects($this->once())
            ->method('getBrandId')
            ->willReturn($brandId);
        $brandMock->expects($this->once())
            ->method('getData')
            ->willReturn($brandData);
        $this->dataPersistorMock->expects($this->once())
            ->method('get')
            ->with('aw_brand')
            ->willReturn(null);

        $this->dataProvider->getData();
        $this->assertEquals([$brandId => $brandData], $this->dataProvider->getData());
    }

    public function testGetDataPersisting()
    {
        $brandId = 1;
        $brandData = [
            'brand_id' => $brandId,
            'attribute_id' => 2
        ];

        $brandMock = $this->createPartialMock(
            Brand::class,
            ['getBrandId', 'getData', 'setData']
        );

        $this->collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([]);
        $this->dataPersistorMock->expects($this->once())
            ->method('get')
            ->with('aw_brand')
            ->willReturn($brandData);
        $this->collectionMock->expects($this->once())
            ->method('getNewEmptyItem')
            ->willReturn($brandMock);
        $brandMock->expects($this->once())
            ->method('setData')
            ->with($brandData);
        $brandMock->expects($this->once())
            ->method('getBrandId')
            ->willReturn($brandId);
        $brandMock->expects($this->once())
            ->method('getData')
            ->willReturn($brandData);
        $this->dataPersistorMock->expects($this->once())
            ->method('clear')
            ->with('aw_brand');

        $this->dataProvider->getData();
        $this->assertEquals([$brandId => $brandData], $this->dataProvider->getData());
    }

    public function testPrepareFormData()
    {
        $imageName = 'logo.png';
        $imageSize = 6097;
        $imageUrl = 'http://localhost/media/aw_sbb/thumbnail/logo.png';
        $mimeType = 'image/png';
        $data = ['logo' => $imageName];
        $expectedResult = [
            'logo' => [
                [
                    'name' => $imageName,
                    'url' => $imageUrl,
                    'size' => $imageSize,
                    'type' => $mimeType
                ]
            ]
        ];

        $this->fileInfoMock->expects($this->once())
            ->method('isExist')
            ->with($imageName)
            ->willReturn(true);
        $this->fileInfoMock->expects($this->once())
            ->method('getStat')
            ->with($imageName)
            ->willReturn(['size' => $imageSize]);
        $this->urlMock->expects($this->once())
            ->method('getLogoUrl')
            ->with($imageName)
            ->willReturn($imageUrl);
        $this->fileInfoMock->expects($this->once())
            ->method('getMimeType')
            ->with($imageName)
            ->willReturn($mimeType);

        $class = new \ReflectionClass($this->dataProvider);
        $method = $class->getMethod('prepareFormData');
        $method->setAccessible(true);

        $this->assertEquals($expectedResult, $method->invokeArgs($this->dataProvider, [$data]));
    }

    public function testPrepareFormDataFileNotExist()
    {
        $imageName = 'logo.png';

        $this->fileInfoMock->expects($this->once())
            ->method('isExist')
            ->with($imageName)
            ->willReturn(false);

        $class = new \ReflectionClass($this->dataProvider);
        $method = $class->getMethod('prepareFormData');
        $method->setAccessible(true);

        $this->assertEquals([], $method->invokeArgs($this->dataProvider, [['logo' => $imageName]]));
    }
}
