<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
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

/**
 * Test for \Aheadworks\ShopByBrand\Controller\Adminhtml\Brand\Delete
 */
class DeleteTest extends \PHPUnit_Framework_TestCase
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
        $this->brandRepositoryMock = $this->getMockForAbstractClass(BrandRepositoryInterface::class);
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

        $resultRedirectMock = $this->getMock(ResultRedirect::class, ['setPath'], [], '', false);

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

        $resultRedirectMock = $this->getMock(ResultRedirect::class, ['setPath'], [], '', false);
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
