<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Controller;

use Aheadworks\ShopByBrand\Controller\Router;
use Aheadworks\ShopByBrand\Model\ResourceModel\Brand as BrandResource;
use Aheadworks\ShopByBrand\Model\Config;
use Aheadworks\ShopByBrand\Model\Url;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\ShopByBrand\Controller\Router
 */
class RouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Constants used in the unit tests
     */
    const BRAND_URL_KEY = 'some_brand';
    const INVALID_URL_KEY = 'invalid_url_key';

    /**
     * @var Router
     */
    private $router;

    /**
     * @var ActionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $actionFactoryMock;

    /**
     * @var BrandResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $brandResourceMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->actionFactoryMock = $this->getMock(ActionFactory::class, ['create'], [], '', false);
        $this->brandResourceMock = $this->getMock(BrandResource::class, ['getBrandIdByUrlKey'], [], '', false);
        $this->configMock = $this->getMock(Config::class, ['getBrandUrlSuffix'], [], '', false);
        $this->requestMock = $this->getMock(
            Http::class,
            [
                'getPathInfo',
                'setAlias',
                'getOriginalPathInfo',
                'setModuleName',
                'setControllerName',
                'setActionName',
                'setParams'
            ],
            [],
            '',
            false
        );
        $this->router = $objectManager->getObject(
            Router::class,
            [
                'actionFactory' => $this->actionFactoryMock,
                'brandResource' => $this->brandResourceMock,
                'config' => $this->configMock
            ]
        );
    }

    /**
     * @param string $pathInfo
     * @param string $suffix
     * @param bool|ActionInterface|\PHPUnit_Framework_MockObject_MockObject $result
     * @dataProvider matchDataProvider
     */
    public function testMatch($pathInfo, $suffix, $result)
    {
        $brandId = 1;

        $this->requestMock->expects($this->once())
            ->method('getPathInfo')
            ->willReturn($pathInfo);
        $this->configMock->expects($this->any())
            ->method('getBrandUrlSuffix')
            ->willReturn($suffix);
        $this->brandResourceMock->expects($this->any())
            ->method('getBrandIdByUrlKey')
            ->willReturnMap(
                [
                    [self::BRAND_URL_KEY, $brandId],
                    [self::INVALID_URL_KEY, null]
                ]
            );
        if ($result !== false) {
            $this->requestMock->expects($this->once())
                ->method('getOriginalPathInfo')
                ->willReturn($pathInfo);
            $this->requestMock->expects($this->once())
                ->method('setAlias')
                ->with(
                    \Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS,
                    $pathInfo
                )->willReturnSelf();
            $this->requestMock->expects($this->once())
                ->method('setModuleName')
                ->with('aw_sbb')
                ->willReturnSelf();
            $this->requestMock->expects($this->once())
                ->method('setControllerName')
                ->with('brand')
                ->willReturnSelf();
            $this->requestMock->expects($this->once())
                ->method('setActionName')
                ->with('view')
                ->willReturnSelf();
            $this->requestMock->expects($this->once())
                ->method('setParams')
                ->with(['brand_id' => $brandId])
                ->willReturnSelf();
            $this->actionFactoryMock->expects($this->once())
                ->method('create')
                ->with(Forward::class)
                ->willReturn($result);
        }

        $this->assertEquals($result, $this->router->match($this->requestMock));
    }

    /**
     * @return array
     */
    public function matchDataProvider()
    {
        return [
            'valid path' => [
                Url::ROUTE_TO_BRAND . '/' . self::BRAND_URL_KEY,
                '',
                $this->getMock(ActionInterface::class)
            ],
            'valid path with suffix' => [
                Url::ROUTE_TO_BRAND . '/' . self::BRAND_URL_KEY . '.html',
                '.html',
                $this->getMock(ActionInterface::class)
            ],
            'missing route to brand part' => [
                self::BRAND_URL_KEY,
                '',
                false
            ],
            'missing url key part' => [
                Url::ROUTE_TO_BRAND,
                '',
                false
            ],
            'invalid url key' => [
                Url::ROUTE_TO_BRAND . '/' . self::INVALID_URL_KEY,
                '',
                false
            ]
        ];
    }
}
