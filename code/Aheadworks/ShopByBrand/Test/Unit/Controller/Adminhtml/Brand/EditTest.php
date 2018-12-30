<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
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
use PHPUnit\Framework\TestCase;

/**
 * Test for \Aheadworks\ShopByBrand\Controller\Adminhtml\Brand\Edit
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditTest extends TestCase
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
        $this->resultPageFactoryMock = $this->createPartialMock(PageFactory::class, ['create']);
        $this->brandFactoryMock = $this->createPartialMock(BrandInterfaceFactory::class, ['create']);
        $this->brandRepositoryMock = $this->getMockBuilder(BrandRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->coreRegistryMock = $this->createPartialMock(Registry::class, ['register']);
        $this->dataObjectProcessorMock = $this->createPartialMock(
            DataObjectProcessor::class,
            ['buildOutputDataArray']
        );
        $this->dataPersistorMock = $this->getMockBuilder(DataPersistorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resultRedirectFactoryMock = $this->createPartialMock(RedirectFactory::class, ['create']);
        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
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

        $brandMock = $this->getMockBuilder(BrandInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $resultPageMock = $this->createPartialMock(Page::class, ['setActiveMenu', 'getConfig']);
        $pageConfigMock = $this->createPartialMock(PageConfig::class, ['getTitle']);
        $titleMock = $this->createPartialMock(Title::class, ['prepend']);

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

        $brandMock = $this->getMockBuilder(BrandInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $resultPageMock = $this->createPartialMock(Page::class, ['setActiveMenu', 'getConfig']);
        $pageConfigMock = $this->createPartialMock(PageConfig::class, ['getTitle']);
        $titleMock = $this->createPartialMock(Title::class, ['prepend']);

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

        $brandMock = $this->getMockBuilder(BrandInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $exception = NoSuchEntityException::singleField('brandId', $brandId);
        $resultRedirectMock = $this->createPartialMock(ResultRedirect::class, ['setPath']);

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
