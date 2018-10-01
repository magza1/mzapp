<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Model;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Model\Brand\Image\Management as ImageManagement;
use Aheadworks\ShopByBrand\Model\Config;
use Aheadworks\ShopByBrand\Model\Url;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\ShopByBrand\Model\Url
 */
class UrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Url
     */
    private $url;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var ImageManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $imageManagementMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->configMock = $this->getMock(Config::class, ['getBrandUrlSuffix'], [], '', false);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->imageManagementMock = $this->getMock(
            ImageManagement::class,
            [
                'getImagePlaceholderUrl',
                'hasImage',
                'createImage',
                'getImageUrl'
            ],
            [],
            '',
            false
        );
        $this->url = $objectManager->getObject(
            Url::class,
            [
                'urlBuilder' => $this->urlBuilderMock,
                'storeManager' => $this->storeManagerMock,
                'imageManagement' => $this->imageManagementMock,
                'config' => $this->configMock
            ]
        );
    }

    public function testGetBrandUrl()
    {
        $urlKey = 'some_brand';
        $suffix = '.html';
        $brandUrl = 'http://localhost/brand/some_brand.html';

        /** @var BrandInterface|\PHPUnit_Framework_MockObject_MockObject $brandMock */
        $brandMock = $this->getMockForAbstractClass(BrandInterface::class);

        $brandMock->expects($this->once())
            ->method('getUrlKey')
            ->willReturn($urlKey);
        $this->configMock->expects($this->once())
            ->method('getBrandUrlSuffix')
            ->willReturn($suffix);
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with(null, ['_direct' => Url::ROUTE_TO_BRAND . '/' . $urlKey . $suffix])
            ->willReturn($brandUrl);
        $this->urlBuilderMock->expects($this->once())
            ->method('setScope')
            ->with(null);

        $this->assertEquals($brandUrl, $this->url->getBrandUrl($brandMock));
    }

    public function testGetBrandUrlForSpecificStore()
    {
        $urlKey = 'some_brand';
        $suffix = '.html';
        $storeId = 1;
        $brandUrl = 'http://localhost/brand/some_brand.html';

        /** @var BrandInterface|\PHPUnit_Framework_MockObject_MockObject $brandMock */
        $brandMock = $this->getMockForAbstractClass(BrandInterface::class);

        $brandMock->expects($this->once())
            ->method('getUrlKey')
            ->willReturn($urlKey);
        $this->configMock->expects($this->once())
            ->method('getBrandUrlSuffix')
            ->willReturn($suffix);
        $this->urlBuilderMock->expects($this->exactly(2))
            ->method('setScope')
            ->withConsecutive([$storeId], [null]);
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with(
                null,
                [
                    '_direct' => Url::ROUTE_TO_BRAND . '/' . $urlKey . $suffix,
                    '_scope' => $storeId
                ]
            )
            ->willReturn($brandUrl);

        $this->assertEquals($brandUrl, $this->url->getBrandUrl($brandMock, $storeId));
    }

    public function testGetLogoUrl()
    {
        $imageName = 'logo.jpg';
        $baseUrl = 'http://localhost/';

        $storeMock = $this->getMock(Store::class, ['getBaseUrl'], [], '', false);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getBaseUrl')
            ->with(UrlInterface::URL_TYPE_MEDIA)
            ->willReturn($baseUrl);

        $this->assertEquals(
            $baseUrl . ImageManagement::IMAGE_PATH . '/' . $imageName,
            $this->url->getLogoUrl($imageName)
        );
    }

    public function testGetLogoUrlForImageTypeEmptyImage()
    {
        $imageType = 'thumbnail';
        $placeholderUrl = 'http://localhost/static/frontend/Magento/blank/en_US/placeholder.png';

        $this->imageManagementMock->expects($this->once())
            ->method('getImagePlaceholderUrl')
            ->with($imageType)
            ->willReturn($placeholderUrl);

        $this->assertEquals($placeholderUrl, $this->url->getLogoUrl('', $imageType));
    }

    public function testGetLogoUrlForImageTypeCreateNew()
    {
        $imageName = 'logo.jpg';
        $imageType = 'thumbnail';
        $placeholderUrl = 'http://localhost/static/frontend/Magento/blank/en_US/placeholder.png';
        $imageUrl = 'http://localhost/media/aw_sbb/thumbnail/logo.jpg';

        $this->imageManagementMock->expects($this->once())
            ->method('getImagePlaceholderUrl')
            ->with($imageType)
            ->willReturn($placeholderUrl);
        $this->imageManagementMock->expects($this->once())
            ->method('hasImage')
            ->with($imageType, $imageName)
            ->willReturn(false);
        $this->imageManagementMock->expects($this->once())
            ->method('createImage')
            ->with($imageType, $imageName);
        $this->imageManagementMock->expects($this->once())
            ->method('getImageUrl')
            ->willReturn($imageUrl);

        $this->assertEquals($imageUrl, $this->url->getLogoUrl($imageName, $imageType));
    }

    public function testGetLogoUrlForImageTypeCreateNewException()
    {
        $imageName = 'logo.jpg';
        $imageType = 'thumbnail';
        $placeholderUrl = 'http://localhost/static/frontend/Magento/blank/en_US/placeholder.png';

        $this->imageManagementMock->expects($this->once())
            ->method('getImagePlaceholderUrl')
            ->with($imageType)
            ->willReturn($placeholderUrl);
        $this->imageManagementMock->expects($this->once())
            ->method('hasImage')
            ->with($imageType, $imageName)
            ->willReturn(false);
        $this->imageManagementMock->expects($this->once())
            ->method('createImage')
            ->with($imageType, $imageName)
            ->willThrowException(new \Exception());

        $this->assertEquals($placeholderUrl, $this->url->getLogoUrl($imageName, $imageType));
    }

    public function testGetLogoUrlForImageTypeExisting()
    {
        $imageName = 'logo.jpg';
        $imageType = 'thumbnail';
        $placeholderUrl = 'http://localhost/static/frontend/Magento/blank/en_US/placeholder.png';
        $imageUrl = 'http://localhost/media/aw_sbb/thumbnail/logo.jpg';

        $this->imageManagementMock->expects($this->once())
            ->method('getImagePlaceholderUrl')
            ->with($imageType)
            ->willReturn($placeholderUrl);
        $this->imageManagementMock->expects($this->once())
            ->method('hasImage')
            ->with($imageType, $imageName)
            ->willReturn(true);
        $this->imageManagementMock->expects($this->never())
            ->method('createImage')
            ->with($imageType, $imageName);
        $this->imageManagementMock->expects($this->once())
            ->method('getImageUrl')
            ->willReturn($imageUrl);

        $this->assertEquals($imageUrl, $this->url->getLogoUrl($imageName, $imageType));
    }
}
