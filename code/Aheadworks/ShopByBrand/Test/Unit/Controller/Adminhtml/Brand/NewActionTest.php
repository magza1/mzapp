<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Controller\Adminhtml\Brand;

use Aheadworks\ShopByBrand\Controller\Adminhtml\Brand\NewAction;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Aheadworks\ShopByBrand\Controller\Adminhtml\Brand\NewAction
 */
class NewActionTest extends TestCase
{
    /**
     * @var NewAction
     */
    private $action;

    /**
     * @var ForwardFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultForwardFactoryMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->resultForwardFactoryMock = $this->createPartialMock(ForwardFactory::class, ['create']);
        $this->action = $objectManager->getObject(
            NewAction::class,
            ['resultForwardFactory' => $this->resultForwardFactoryMock]
        );
    }

    public function testExecute()
    {
        $forwardMock = $this->createPartialMock(Forward::class, ['forward']);
        $this->resultForwardFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($forwardMock);
        $forwardMock->expects($this->once())
            ->method('forward')
            ->with('edit')
            ->willReturnSelf();
        $this->assertSame($forwardMock, $this->action->execute());
    }
}
