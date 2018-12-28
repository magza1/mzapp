<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Model\Brand\Image;

use Aheadworks\ShopByBrand\Model\Brand\Image\Management;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Image;
use Magento\Framework\Image\Factory as ImageFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Aheadworks\ShopByBrand\Model\Brand\Image\Management
 */
class ManagementTest extends TestCase
{
    /**
     * Constants used in the unit tests
     */
    const IMAGE_TYPE = 'thumbnail';
    const IMAGE_PATH = 'aw_sbb/thumbnail/brand';
    const IMAGE_PLACEHOLDER_PATH = 'Magento_Catalog::images/product/placeholder/thumbnail.jpg';
    const IMAGE_SIZE = 75;

    /**
     * @var Management
     */
    private $imageManagement;

    /**
     * @var ImageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $imageProcessorFactoryMock;

    /**
     * @var WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mediaDirectoryMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var AssetRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assetRepoMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->imageProcessorFactoryMock = $this->createPartialMock(ImageFactory::class, ['create']);
        $filesystemMock = $this->createPartialMock(Filesystem::class, ['getDirectoryWrite']);
        $this->mediaDirectoryMock = $this->getMockBuilder(WriteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->assetRepoMock = $this->createPartialMock(AssetRepository::class, ['getUrl']);

        $filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->mediaDirectoryMock);

        $this->imageManagement = $objectManager->getObject(
            Management::class,
            [
                'imageProcessorFactory' => $this->imageProcessorFactoryMock,
                'filesystem' => $filesystemMock,
                'storeManager' => $this->storeManagerMock,
                'assetRepo' => $this->assetRepoMock,
                'imageTypes' => [
                    self::IMAGE_TYPE => [
                        'path' => self::IMAGE_PATH,
                        'placeholderPath' => self::IMAGE_PLACEHOLDER_PATH,
                        'imageSize' => self::IMAGE_SIZE
                    ]
                ]
            ]
        );
    }

    /**
     * @param bool $isExist
     * @dataProvider boolDataProvider
     */
    public function testHasImage($isExist)
    {
        $fileName = 'logo.png';
        $filePath = self::IMAGE_PATH . '/' . $fileName;
        $this->mediaDirectoryMock->expects($this->once())
            ->method('isExist')
            ->with($filePath)
            ->willReturn($isExist);
        $this->assertEquals($isExist, $this->imageManagement->hasImage(self::IMAGE_TYPE, $fileName));
    }

    public function testCreateImage()
    {
        $fileName = 'logo.png';
        $filePath = self::IMAGE_PATH . '/' . $fileName;
        $absoluteFilePath = 'http://localhost/pub/media/aw_sbb/brand/logo.png';

        $imageProcessorMock = $this->createPartialMock(
            Image::class,
            [
                'keepAspectRatio',
                'keepFrame',
                'keepTransparency',
                'backgroundColor',
                'constrainOnly',
                'quality',
                'resize',
                'save'
            ]
        );

        $this->mediaDirectoryMock->expects($this->once())
            ->method('copyFile')
            ->with(
                Management::IMAGE_PATH . '/' . $fileName,
                $filePath
            );
        $this->mediaDirectoryMock->expects($this->once())
            ->method('getAbsolutePath')
            ->with($filePath)
            ->willReturn($absoluteFilePath);
        $this->imageProcessorFactoryMock->expects($this->once())
            ->method('create')
            ->with($absoluteFilePath)
            ->willReturn($imageProcessorMock);
        $imageProcessorMock->expects($this->once())
            ->method('keepAspectRatio')
            ->with(true);
        $imageProcessorMock->expects($this->once())
            ->method('keepFrame')
            ->with(true);
        $imageProcessorMock->expects($this->once())
            ->method('keepTransparency')
            ->with(true);
        $imageProcessorMock->expects($this->once())
            ->method('backgroundColor')
            ->with([255, 255, 255]);
        $imageProcessorMock->expects($this->once())
            ->method('constrainOnly')
            ->with(true);
        $imageProcessorMock->expects($this->once())
            ->method('quality')
            ->with(80);
        $imageProcessorMock->expects($this->once())
            ->method('resize')
            ->with(self::IMAGE_SIZE);
        $imageProcessorMock->expects($this->once())
            ->method('save');

        $this->imageManagement->createImage(self::IMAGE_TYPE, $fileName);
    }

    public function testGetImageUrl()
    {
        $fileName = 'logo.png';
        $filePath = self::IMAGE_PATH . '/' . $fileName;
        $baseUrl = 'http://localhost/';

        $storeMock = $this->createPartialMock(Store::class, ['getBaseUrl']);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getBaseUrl')
            ->with(UrlInterface::URL_TYPE_MEDIA)
            ->willReturn($baseUrl);

        $this->assertEquals(
            $baseUrl . $filePath,
            $this->imageManagement->getImageUrl(self::IMAGE_TYPE, $fileName)
        );
    }

    public function testGetImagePlaceholderUrl()
    {
        $placeholderUrl = 'http://localhost/static/frontend/Magento/blank/en_US/placeholder.png';
        $this->assetRepoMock->expects($this->once())
            ->method('getUrl')
            ->with(self::IMAGE_PLACEHOLDER_PATH)
            ->willReturn($placeholderUrl);
        $this->assertEquals($placeholderUrl, $this->imageManagement->getImagePlaceholderUrl(self::IMAGE_TYPE));
    }

    /**
     * @return array
     */
    public function boolDataProvider()
    {
        return [[true], [false]];
    }
}
