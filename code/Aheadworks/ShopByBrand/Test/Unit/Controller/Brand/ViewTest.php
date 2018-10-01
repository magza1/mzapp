<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Controller\Brand;

use Aheadworks\ShopByBrand\Api\BrandRepositoryInterface;
use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Controller\Brand\View;
use Aheadworks\ShopByBrand\Model\Brand\PageConfig;
use Aheadworks\ShopByBrand\Model\Config;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\Page;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\ShopByBrand\Controller\Brand\View
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var View
     */
    private $action;

    /**
     * @var BrandRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $brandRepositoryMock;

    /**
     * @var LayerResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $layerResolverMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var PageConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $brandPageConfigMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * @var RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redirectMock;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->brandRepositoryMock = $this->getMockForAbstractClass(BrandRepositoryInterface::class);
        $this->layerResolverMock = $this->getMock(LayerResolver::class, ['create'], [], '', false);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->configMock = $this->getMock(Config::class, ['getBrandProductAttributeCode'], [], '', false);
        $this->brandPageConfigMock = $this->getMock(PageConfig::class, ['apply'], [], '', false);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->resultFactoryMock = $this->getMock(ResultFactory::class, ['create'], [], '', false);
        $this->resultRedirectFactoryMock = $this->getMock(RedirectFactory::class, ['create'], [], '', false);
        $this->redirectMock = $this->getMockForAbstractClass(RedirectInterface::class);
        $this->messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $context = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
                'resultFactory' => $this->resultFactoryMock,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock,
                'redirect' => $this->redirectMock,
                'messageManager' => $this->messageManagerMock
            ]
        );
        $this->action = $objectManager->getObject(
            View::class,
            [
                'context' => $context,
                'brandRepository' => $this->brandRepositoryMock,
                'layerResolver' => $this->layerResolverMock,
                'storeManager' => $this->storeManagerMock,
                'config' => $this->configMock,
                'brandPageConfig' => $this->brandPageConfigMock
            ]
        );
    }

    public function testExecute()
    {
        $brandId = 1;
        $websiteId = 2;
        $attributeCode = 'manufacturer';

        $brandMock = $this->getMockForAbstractClass(BrandInterface::class);
        $websiteMock = $this->getMockForAbstractClass(WebsiteInterface::class);
        $resultPageMock = $this->getMock(Page::class, [], [], '', false);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('brand_id')
            ->willReturn($brandId);
        $this->brandRepositoryMock->expects($this->once())
            ->method('get')
            ->with($brandId)
            ->willReturn($brandMock);
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willReturn($websiteMock);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn($websiteId);
        $brandMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $this->configMock->expects($this->once())
            ->method('getBrandProductAttributeCode')
            ->willReturn($attributeCode);
        $brandMock->expects($this->once())
            ->method('getWebsiteIds')
            ->willReturn([$websiteId]);
        $this->layerResolverMock->expects($this->once())
            ->method('create')
            ->with('aw_brand');
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_PAGE)
            ->willReturn($resultPageMock);
        $this->brandPageConfigMock->expects($this->once())
            ->method('apply')
            ->with($resultPageMock, $brandMock)
            ->willReturn($resultPageMock);

        $this->assertSame($resultPageMock, $this->action->execute());
    }

    public function testExecuteWrongAttributeCode()
    {
        $brandId = 1;
        $websiteId = 2;
        $brandAttributeCode = 'manufacturer';
        $configAttributeCode = 'brand';

        $brandMock = $this->getMockForAbstractClass(BrandInterface::class);
        $websiteMock = $this->getMockForAbstractClass(WebsiteInterface::class);
        $resultForwardMock = $this->getMock(
            Forward::class,
            ['setModule', 'setController', 'forward'],
            [],
            '',
            false
        );

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('brand_id')
            ->willReturn($brandId);
        $this->brandRepositoryMock->expects($this->once())
            ->method('get')
            ->with($brandId)
            ->willReturn($brandMock);
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willReturn($websiteMock);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn($websiteId);
        $brandMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($brandAttributeCode);
        $this->configMock->expects($this->once())
            ->method('getBrandProductAttributeCode')
            ->willReturn($configAttributeCode);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_FORWARD)
            ->willReturn($resultForwardMock);
        $resultForwardMock->expects($this->once())
            ->method('setModule')
            ->with('cms')
            ->willReturnSelf();
        $resultForwardMock->expects($this->once())
            ->method('setController')
            ->with('noroute')
            ->willReturnSelf();
        $resultForwardMock->expects($this->once())
            ->method('forward')
            ->with('index')
            ->willReturnSelf();

        $this->assertSame($resultForwardMock, $this->action->execute());
    }

    public function testExecuteWrongWebsite()
    {
        $brandId = 1;
        $websiteId = 2;
        $brandWebsiteId = 3;
        $attributeCode = 'manufacturer';

        $brandMock = $this->getMockForAbstractClass(BrandInterface::class);
        $websiteMock = $this->getMockForAbstractClass(WebsiteInterface::class);
        $resultForwardMock = $this->getMock(
            Forward::class,
            ['setModule', 'setController', 'forward'],
            [],
            '',
            false
        );

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('brand_id')
            ->willReturn($brandId);
        $this->brandRepositoryMock->expects($this->once())
            ->method('get')
            ->with($brandId)
            ->willReturn($brandMock);
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willReturn($websiteMock);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn($websiteId);
        $brandMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $this->configMock->expects($this->once())
            ->method('getBrandProductAttributeCode')
            ->willReturn($attributeCode);
        $brandMock->expects($this->once())
            ->method('getWebsiteIds')
            ->willReturn([$brandWebsiteId]);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_FORWARD)
            ->willReturn($resultForwardMock);
        $resultForwardMock->expects($this->once())
            ->method('setModule')
            ->with('cms')
            ->willReturnSelf();
        $resultForwardMock->expects($this->once())
            ->method('setController')
            ->with('noroute')
            ->willReturnSelf();
        $resultForwardMock->expects($this->once())
            ->method('forward')
            ->with('index')
            ->willReturnSelf();

        $this->assertSame($resultForwardMock, $this->action->execute());
    }

    public function testExecuteException()
    {
        $brandId = 1;
        $exceptionMessage = 'Exception message';
        $refererUrl = 'http://localhost/';

        $resultRedirectMock = $this->getMock(Redirect::class, ['setUrl'], [], '', false);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('brand_id')
            ->willReturn($brandId);
        $this->brandRepositoryMock->expects($this->once())
            ->method('get')
            ->with($brandId)
            ->willThrowException(new LocalizedException(__($exceptionMessage)));
        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with($exceptionMessage);
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);
        $this->redirectMock->expects($this->once())
            ->method('getRefererUrl')
            ->willReturn($refererUrl);
        $resultRedirectMock->expects($this->once())
            ->method('setUrl')
            ->with($refererUrl);

        $this->assertSame($resultRedirectMock, $this->action->execute());
    }
}
