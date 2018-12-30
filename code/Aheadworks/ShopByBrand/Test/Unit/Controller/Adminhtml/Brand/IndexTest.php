<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Controller\Adminhtml\Brand;

use Aheadworks\ShopByBrand\Controller\Adminhtml\Brand\Index;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\View\Page\Title;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\Result\PageFactory;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Aheadworks\ShopByBrand\Controller\Adminhtml\Brand\Index
 */
class IndexTest extends TestCase
{
    /**
     * @var Index
     */
    private $action;

    /**
     * @var PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultPageFactoryMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->resultPageFactoryMock = $this->createPartialMock(PageFactory::class, ['create']);
        $this->action = $objectManager->getObject(
            Index::class,
            ['resultPageFactory' => $this->resultPageFactoryMock]
        );
    }

    public function testExecute()
    {
        $resultPageMock = $this->createPartialMock(Page::class, ['setActiveMenu', 'getConfig']);
        $pageConfigMock = $this->createPartialMock(PageConfig::class, ['getTitle']);
        $titleMock = $this->createPartialMock(Title::class, ['prepend']);

        $this->resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultPageMock);
        $resultPageMock->expects($this->once())
            ->method('setActiveMenu')
            ->with('Aheadworks_ShopByBrand::brands');
        $resultPageMock->expects($this->once())
            ->method('getConfig')
            ->willReturn($pageConfigMock);
        $pageConfigMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($titleMock);
        $titleMock->expects($this->once())
            ->method('prepend')
            ->with('Brand Pages');

        $this->assertSame($resultPageMock, $this->action->execute());
    }
}
