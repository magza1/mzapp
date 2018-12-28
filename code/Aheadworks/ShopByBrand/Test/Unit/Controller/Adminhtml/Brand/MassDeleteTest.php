<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Controller\Adminhtml\Brand;

use Aheadworks\ShopByBrand\Api\BrandRepositoryInterface;
use Aheadworks\ShopByBrand\Controller\Adminhtml\Brand\MassDelete;
use Aheadworks\ShopByBrand\Model\ResourceModel\Brand\Collection;
use Aheadworks\ShopByBrand\Model\ResourceModel\Brand\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Ui\Component\MassAction\Filter;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Aheadworks\ShopByBrand\Controller\Adminhtml\Brand\MassDelete
 */
class MassDeleteTest extends TestCase
{
    /**
     * @var MassDelete
     */
    private $action;

    /**
     * @var Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterMock;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var BrandRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $brandRepositoryMock;

    /**
     * @var ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->filterMock = $this->createPartialMock(Filter::class, ['getCollection']);
        $this->collectionFactoryMock = $this->createPartialMock(CollectionFactory::class, ['create']);
        $this->brandRepositoryMock = $this->getMockBuilder(BrandRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resultFactoryMock = $this->createPartialMock(ResultFactory::class, ['create']);
        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $context = $objectManager->getObject(
            Context::class,
            [
                'resultFactory' => $this->resultFactoryMock,
                'messageManager' => $this->messageManagerMock
            ]
        );
        $this->action = $objectManager->getObject(
            MassDelete::class,
            [
                'context' => $context,
                'filter' => $this->filterMock,
                'collectionFactory' => $this->collectionFactoryMock,
                'brandRepository' => $this->brandRepositoryMock
            ]
        );
    }

    public function testExecute()
    {
        $brandIds = [1, 3];
        $collectionSize = 2;

        $collectionMock = $this->createPartialMock(Collection::class, ['getSize', 'getAllIds']);
        $resultRedirectMock = $this->createPartialMock(ResultRedirect::class, ['setPath']);

        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);
        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($collectionMock)
            ->willReturn($collectionMock);
        $collectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn($collectionSize);
        $collectionMock->expects($this->once())
            ->method('getAllIds')
            ->willReturn($brandIds);
        $this->brandRepositoryMock->expects($this->exactly($collectionSize))
            ->method('deleteById')
            ->withConsecutive([1], [3]);
        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with('A total of 2 record(s) have been deleted.');
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($resultRedirectMock);
        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($resultRedirectMock, $this->action->execute());
    }

    public function testExecuteException()
    {
        $brandId = 1;
        $collectionSize = 5;

        $collectionMock = $this->createPartialMock(Collection::class, ['getSize', 'getAllIds']);
        $exception = new \Exception('Exception message.');
        $resultRedirectMock = $this->createPartialMock(ResultRedirect::class, ['setPath']);

        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);
        $this->filterMock->expects($this->once())
            ->method('getCollection')
            ->with($collectionMock)
            ->willReturn($collectionMock);
        $collectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn($collectionSize);
        $collectionMock->expects($this->once())
            ->method('getAllIds')
            ->willReturn([$brandId]);
        $this->brandRepositoryMock->expects($this->once())
            ->method('deleteById')
            ->with($brandId)
            ->willThrowException($exception);
        $this->messageManagerMock->expects($this->once())
            ->method('addExceptionMessage')
            ->with($exception, 'Something went wrong while deleting the items.');
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($resultRedirectMock);
        $resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($resultRedirectMock, $this->action->execute());
    }
}
