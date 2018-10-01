<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Model\Brand;

use Aheadworks\ShopByBrand\Model\Brand\FileInfo;
use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\ShopByBrand\Model\Brand\FileInfo
 */
class FileInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Constants used in the unit tests
     */
    const FILE_NAME = 'logo.png';
    const BASE_PATH = 'aw_sbb/brand';
    const FILE_PATH = 'aw_sbb/brand/logo.png';

    /**
     * @var FileInfo
     */
    private $fileInfo;

    /**
     * @var ImageUploader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $imageUploaderMock;

    /**
     * @var WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mediaDirectoryMock;

    /**
     * @var Mime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mimeMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $filesystemMock = $this->getMock(Filesystem::class, ['getDirectoryWrite'], [], '', false);
        $this->imageUploaderMock = $this->getMock(
            ImageUploader::class,
            ['getFilePath', 'getBasePath'],
            [],
            '',
            false
        );
        $this->mediaDirectoryMock = $this->getMockForAbstractClass(WriteInterface::class);
        $this->mimeMock = $this->getMock(Mime::class, ['getMimeType'], [], '', false);

        $filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->mediaDirectoryMock);

        $this->fileInfo = $objectManager->getObject(
            FileInfo::class,
            [
                'imageUploader' => $this->imageUploaderMock,
                'filesystem' => $filesystemMock,
                'mime' => $this->mimeMock
            ]
        );
    }

    public function testGetMimeType()
    {
        $absoluteFilePath = 'http://localhost/pub/media/aw_sbb/brand/logo.png';
        $mimeType = 'image/png';

        $this->imageUploaderMock->expects($this->once())
            ->method('getBasePath')
            ->willReturn(self::BASE_PATH);
        $this->imageUploaderMock->expects($this->once())
            ->method('getFilePath')
            ->with(self::BASE_PATH, self::FILE_NAME)
            ->willReturn(self::FILE_PATH);
        $this->mediaDirectoryMock->expects($this->once())
            ->method('getAbsolutePath')
            ->with(self::FILE_PATH)
            ->willReturn($absoluteFilePath);
        $this->mimeMock->expects($this->once())
            ->method('getMimeType')
            ->with($absoluteFilePath)
            ->willReturn($mimeType);

        $this->assertEquals($mimeType, $this->fileInfo->getMimeType(self::FILE_NAME));
    }

    public function testGetStat()
    {
        $stat = ['size' => 6097];

        $this->imageUploaderMock->expects($this->once())
            ->method('getBasePath')
            ->willReturn(self::BASE_PATH);
        $this->imageUploaderMock->expects($this->once())
            ->method('getFilePath')
            ->with(self::BASE_PATH, self::FILE_NAME)
            ->willReturn(self::FILE_PATH);
        $this->mediaDirectoryMock->expects($this->once())
            ->method('stat')
            ->willReturn($stat);

        $this->assertEquals($stat, $this->fileInfo->getStat(self::FILE_NAME));
    }

    public function testIsExist()
    {
        $isExist = true;

        $this->imageUploaderMock->expects($this->once())
            ->method('getBasePath')
            ->willReturn(self::BASE_PATH);
        $this->imageUploaderMock->expects($this->once())
            ->method('getFilePath')
            ->with(self::BASE_PATH, self::FILE_NAME)
            ->willReturn(self::FILE_PATH);
        $this->mediaDirectoryMock->expects($this->once())
            ->method('isExist')
            ->with(self::FILE_PATH)
            ->willReturn($isExist);

        $this->assertEquals($isExist, $this->fileInfo->isExist(self::FILE_NAME));
    }
}
