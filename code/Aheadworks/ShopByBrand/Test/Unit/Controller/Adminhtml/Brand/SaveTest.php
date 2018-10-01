<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Controller\Adminhtml\Brand;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Api\Data\BrandInterfaceFactory;
use Aheadworks\ShopByBrand\Api\BrandRepositoryInterface;
use Aheadworks\ShopByBrand\Controller\Adminhtml\Brand\Save;
use Aheadworks\ShopByBrand\Model\Brand;
use Aheadworks\ShopByBrand\Model\Brand\PostDataProcessor\Composite as PostDataProcessor;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Backend\Model\View\Result\Redirect as ResultRedirect;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\ShopByBrand\Controller\Adminhtml\Brand\Save
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Save
     */
    private $action;

    /**
     * @var DataPersistorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataPersistorMock;

    /**
     * @var PostDataProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $postDataProcessorMock;

    /**
     * @var BrandRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $brandRepositoryMock;

    /**
     * @var BrandInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $brandFactoryMock;

    /**
     * @var DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var Http|\PHPUnit_Framework_MockObject_MockObject
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
        $this->dataPersistorMock = $this->getMockForAbstractClass(DataPersistorInterface::class);
        $this->postDataProcessorMock = $this->getMock(
            PostDataProcessor::class,
            ['prepareEntityData'],
            [],
            '',
            false
        );
        $this->brandFactoryMock = $this->getMock(BrandInterfaceFactory::class, ['create'], [], '', false);
        $this->brandRepositoryMock = $this->getMockForAbstractClass(BrandRepositoryInterface::class);
        $this->dataObjectHelperMock = $this->getMock(
            DataObjectHelper::class,
            ['populateWithArray'],
            [],
            '',
            false
        );
        $this->requestMock = $this->getMock(Http::class, ['getPostValue', 'getParam'], [], '', false);
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
            Save::class,
            [
                'context' => $context,
                'dataPersistor' => $this->dataPersistorMock,
                'postDataProcessor' => $this->postDataProcessorMock,
                'brandFactory' => $this->brandFactoryMock,
                'brandRepository' => $this->brandRepositoryMock,
                'dataObjectHelper' => $this->dataObjectHelperMock
            ]
        );
    }

    public function testExecuteNew()
    {
        $requestData = [
            'brand_id' => null,
            'field' => 'value'
        ];

        $resultRedirectMock = $this->getMock(ResultRedirect::class, ['setPath'], [], '', false);
        $brandMock = $this->getMockForAbstractClass(BrandInterface::class);

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($requestData);
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);
        $this->postDataProcessorMock->expects($this->once())
            ->method('prepareEntityData')
            ->with($requestData)
            ->willReturnArgument(0);
        $this->brandFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($brandMock);
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with(
                $brandMock,
                $requestData,
                BrandInterface::class
            );
        $this->brandRepositoryMock->expects($this->once())
            ->method('save')
            ->with($brandMock)
            ->willReturn($brandMock);
        $this->dataPersistorMock->expects($this->once())
            ->method('clear')
            ->with('aw_brand');
        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with('The brand was successfully saved.');
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('back')
            ->willReturn(null);
        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($resultRedirectMock, $this->action->execute());
    }

    public function testExecuteNewException()
    {
        $requestData = [
            'brand_id' => null,
            'field' => 'value'
        ];

        $resultRedirectMock = $this->getMock(ResultRedirect::class, ['setPath'], [], '', false);
        $brandMock = $this->getMockForAbstractClass(BrandInterface::class);
        $exception = new \Exception('Exception message');

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($requestData);
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);
        $this->postDataProcessorMock->expects($this->once())
            ->method('prepareEntityData')
            ->with($requestData)
            ->willReturnArgument(0);
        $this->brandFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($brandMock);
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with(
                $brandMock,
                $requestData,
                BrandInterface::class
            );
        $this->brandRepositoryMock->expects($this->once())
            ->method('save')
            ->with($brandMock)
            ->willThrowException($exception);
        $this->messageManagerMock->expects($this->once())
            ->method('addExceptionMessage')
            ->with($exception, 'Something went wrong while saving the brand.');
        $this->dataPersistorMock->expects($this->once())
            ->method('set')
            ->with('aw_brand', $requestData);
        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/new', ['_current' => true])
            ->willReturnSelf();

        $this->assertSame($resultRedirectMock, $this->action->execute());
    }

    public function testExecuteExisting()
    {
        $brandId = 1;
        $requestData = [
            'brand_id' => $brandId,
            'field' => 'value'
        ];

        $resultRedirectMock = $this->getMock(ResultRedirect::class, ['setPath'], [], '', false);
        $brandMock = $this->getMockForAbstractClass(BrandInterface::class);

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($requestData);
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);
        $this->postDataProcessorMock->expects($this->once())
            ->method('prepareEntityData')
            ->with($requestData)
            ->willReturnArgument(0);
        $this->brandRepositoryMock->expects($this->once())
            ->method('get')
            ->with($brandId)
            ->willReturn($brandMock);
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with(
                $brandMock,
                $requestData,
                BrandInterface::class
            );
        $this->brandRepositoryMock->expects($this->once())
            ->method('save')
            ->with($brandMock)
            ->willReturn($brandMock);
        $this->dataPersistorMock->expects($this->once())
            ->method('clear')
            ->with('aw_brand');
        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with('The brand was successfully saved.');
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('back')
            ->willReturn(null);
        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($resultRedirectMock, $this->action->execute());
    }

    public function testExecuteExistingException()
    {
        $brandId = 1;
        $requestData = [
            'brand_id' => $brandId,
            'field' => 'value'
        ];

        $resultRedirectMock = $this->getMock(ResultRedirect::class, ['setPath'], [], '', false);
        $brandMock = $this->getMockForAbstractClass(BrandInterface::class);
        $exception = new \Exception('Exception message');

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($requestData);
        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);
        $this->postDataProcessorMock->expects($this->once())
            ->method('prepareEntityData')
            ->with($requestData)
            ->willReturnArgument(0);
        $this->brandRepositoryMock->expects($this->once())
            ->method('get')
            ->with($brandId)
            ->willReturn($brandMock);
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with(
                $brandMock,
                $requestData,
                BrandInterface::class
            );
        $this->brandRepositoryMock->expects($this->once())
            ->method('save')
            ->with($brandMock)
            ->willThrowException($exception);
        $this->messageManagerMock->expects($this->once())
            ->method('addExceptionMessage')
            ->with($exception, 'Something went wrong while saving the brand.');
        $this->dataPersistorMock->expects($this->once())
            ->method('set')
            ->with('aw_brand', $requestData);
        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with(
                '*/*/edit',
                [
                    'brand_id' => $brandId,
                    '_current' => true
                ]
            )
            ->willReturnSelf();

        $this->assertSame($resultRedirectMock, $this->action->execute());
    }
}
