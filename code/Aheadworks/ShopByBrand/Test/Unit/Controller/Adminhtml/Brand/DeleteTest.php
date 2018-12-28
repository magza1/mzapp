<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Controller\Adminhtml\Brand;

use Aheadworks\ShopByBrand\Api\BrandRepositoryInterface;
use Aheadworks\ShopByBrand\Controller\Adminhtml\Brand\Delete;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Backend\Model\View\Result\Redirect as ResultRedirect;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Aheadworks\ShopByBrand\Controller\Adminhtml\Brand\Delete
 */
class DeleteTest extends TestCase
{
    /**
     * @var Delete
     */
    private $action;

    /**
     * @var BrandRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $brandRepositoryMock;

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
        $this->brandRepositoryMock = $this->getMockBuilder(BrandRepositoryInterface::class)
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
            Delete::class,
            [
                'context' => $context,
                'brandRepository' => $this->brandRepositoryMock
            ]
        );
    }

    public function testExecute()
    {
        $brandId = 1;

        $resultRedirectMock = $this->createPartialMock(ResultRedirect::class, ['setPath']);

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('brand_id')
            ->willReturn($brandId);
        $this->brandRepositoryMock->expects($this->once())
            ->method('deleteById')
            ->with($brandId);
        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with('Brand was successfully deleted.');
        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($resultRedirectMock, $this->action->execute());
    }

    public function testExecuteException()
    {
        $brandId = 1;

        $resultRedirectMock = $this->createPartialMock(ResultRedirect::class, ['setPath']);
        $exception = new \Exception('Exception message.');

        $this->resultRedirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultRedirectMock);
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('brand_id')
            ->willReturn($brandId);
        $this->brandRepositoryMock->expects($this->once())
            ->method('deleteById')
            ->with($brandId)
            ->willThrowException($exception);
        $this->messageManagerMock->expects($this->once())
            ->method('addExceptionMessage')
            ->with($exception, 'Brand could not be deleted.');
        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($resultRedirectMock, $this->action->execute());
    }
}
