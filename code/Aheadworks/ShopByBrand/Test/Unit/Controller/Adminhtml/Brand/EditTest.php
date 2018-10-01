<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Controller\Adminhtml\Brand;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Api\Data\BrandInterfaceFactory;
use Aheadworks\ShopByBrand\Api\BrandRepositoryInterface;
use Aheadworks\ShopByBrand\Controller\Adminhtml\Brand\Edit;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Backend\Model\View\Result\Redirect as ResultRedirect;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Page\Title;
use Magento\Framework\Registry;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\Result\PageFactory;

/**
 * Test for \Aheadworks\ShopByBrand\Controller\Adminhtml\Brand\Edit
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Edit
     */
    private $action;

    /**
     * @var PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultPageFactoryMock;

    /**
     * @var BrandInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $brandFactoryMock;

    /**
     * @var BrandRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $brandRepositoryMock;

    /**
     * @var Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $coreRegistryMock;

    /**
     * @var DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectProcessorMock;

    /**
     * @var DataPersistorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataPersistorMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->resultPageFactoryMock = $this->getMock(PageFactory::class, ['create'], [], '', false);
        $this->brandFactoryMock = $this->getMock(BrandInterfaceFactory::class, ['create'], [], '', false);
        $this->brandRepositoryMock = $this->getMockForAbstractClass(BrandRepositoryInterface::class);
        $this->coreRegistryMock = $this->getMock(Registry::class, ['register'], [], '', false);
        $this->dataObjectProcessorMock = $this->getMock(
            DataObjectProcessor::class,
            ['buildOutputDataArray'],
            [],
            '',
            false
        );
        $this->dataPersistorMock = $this->getMockForAbstractClass(DataPersistorInterface::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->resultRedirectFactoryMock = $this->getMock(RedirectFactory::class, ['create'], [], '', false);
        $this->messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $context = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock,
                'messageManager' => $this->messageManagerMock
            ]
        );
        $this->action = $objectManager->getObject(
            Edit::class,
            [
                'context' => $context,
                'resultPageFactory' => $this->resultPageFactoryMock,
                'brandFactory' => $this->brandFactoryMock,
                'brandRepository' => $this->brandRepositoryMock,
                'coreRegistry' => $this->coreRegistryMock,
                'dataObjectProcessor' => $this->dataObjectProcessorMock,
                'dataPersistor' => $this->dataPersistorMock
            ]
        );
    }

    public function testExecuteExisting()
    {
        $brandId = 1;
        $contentData = [['contentField' => 'contentValue']];
        $brandData = ['content' => $contentData];

        $brandMock = $this->getMockForAbstractClass(BrandInterface::class);
        $resultPageMock = $this->getMock(Page::class, ['setActiveMenu', 'getConfig'], [], '', false);
        $pageConfigMock = $this->getMock(PageConfig::class, ['getTitle'], [], '', false);
        $titleMock = $this->getMock(Title::class, ['prepend'], [], '', false);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('brand_id')
            ->willReturn($brandId);
        $this->brandFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($brandMock);
        $this->brandRepositoryMock->expects($this->once())
            ->method('get')
            ->with($brandId)
            ->willReturn($brandMock);
        $this->dataObjectProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($brandMock, BrandInterface::class)
            ->willReturn($brandData);
        $this->coreRegistryMock->expects($this->once())
            ->method('register')
            ->with('aw_brand_content', $contentData);
        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultPageMock);
        $resultPageMock->expects($this->once())
            ->method('setActiveMenu')
            ->with('Aheadworks_ShopByBrand::brands')
            ->willReturnSelf();
        $resultPageMock->expects($this->once())
            ->method('getConfig')
            ->willReturn($pageConfigMock);
        $pageConfigMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($titleMock);
        $titleMock->expects($this->once())
            ->method('prepend')
            ->with('Edit Brand');

        $this->assertSame($resultPageMock, $this->action->execute());
    }

    public function testExecuteNew()
    {
        $brandData = ['field' => null];

        $brandMock = $this->getMockForAbstractClass(BrandInterface::class);
        $resultPageMock = $this->getMock(Page::class, ['setActiveMenu', 'getConfig'], [], '', false);
        $pageConfigMock = $this->getMock(PageConfig::class, ['getTitle'], [], '', false);
        $titleMock = $this->getMock(Title::class, ['prepend'], [], '', false);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('brand_id')
            ->willReturn(null);
        $this->brandFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($brandMock);
        $this->dataObjectProcessorMock->expects($this->once())
            ->method('buildOutputDataArray')
            ->with($brandMock, BrandInterface::class)
            ->willReturn($brandData);
        $this->coreRegistryMock->expects($this->once())
            ->method('register')
            ->with('aw_brand_content', []);
        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultPageMock);
        $resultPageMock->expects($this->once())
            ->method('setActiveMenu')
            ->with('Aheadworks_ShopByBrand::brands')
            ->willReturnSelf();
        $resultPageMock->expects($this->once())
            ->method('getConfig')
            ->willReturn($pageConfigMock);
        $pageConfigMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($titleMock);
        $titleMock->expects($this->once())
            ->method('prepend')
            ->with('New Brand');

        $this->assertSame($resultPageMock, $this->action->execute());
    }

    public function testExecuteException()
    {
        $brandId = 1;

        $brandMock = $this->getMockForAbstractClass(BrandInterface::class);
        $exception = NoSuchEntityException::singleField('brandId', $brandId);
        $resultRedirectMock = $this->getMock(ResultRedirect::class, ['setPath'], [], '', false);

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('brand_id')
            ->willReturn($brandId);
        $this->brandFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($brandMock);
        $this->brandRepositoryMock->expects($this->once())
            ->method('get')
            ->with($brandId)
            ->willThrowException($exception);
        $this->messageManagerMock->expects($this->once())
            ->method('addExceptionMessage')
            ->with($exception, 'Something went wrong while editing the brand.');
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);
        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/');

        $this->assertSame($resultRedirectMock, $this->action->execute());
    }
}
