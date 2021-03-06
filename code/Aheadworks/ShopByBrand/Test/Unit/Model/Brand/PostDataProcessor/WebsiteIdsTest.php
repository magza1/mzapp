<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Model\Brand\PostDataProcessor;

use Aheadworks\ShopByBrand\Model\Brand\PostDataProcessor\WebsiteIds;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Api\WebsiteManagementInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Test for \Aheadworks\ShopByBrand\Model\Brand\PostDataProcessor\WebsiteIds
 */
class WebsiteIdsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WebsiteIds
     */
    private $dataProcessor;

    /**
     * @var WebsiteManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $websiteManagementMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->websiteManagementMock = $this->getMockForAbstractClass(WebsiteManagementInterface::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->dataProcessor = $objectManager->getObject(
            WebsiteIds::class,
            [
                'websiteManagement' => $this->websiteManagementMock,
                'storeManager' => $this->storeManagerMock
            ]
        );
    }

    public function testPrepareEntityDataSingleWebsite()
    {
        $websiteId = 1;
        $data = ['field' => 'value'];
        $expectedResult = [
            'field' => 'value',
            'website_ids' => [$websiteId]
        ];

        $websiteMock = $this->getMockForAbstractClass(WebsiteInterface::class);

        $this->websiteManagementMock->expects($this->once())
            ->method('getCount')
            ->willReturn(1);
        $this->storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->willReturn($websiteMock);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn($websiteId);

        $this->assertEquals($expectedResult, $this->dataProcessor->prepareEntityData($data));
    }

    public function testPrepareEntityDataMultiWebsite()
    {
        $data = ['field' => 'value'];

        $this->websiteManagementMock->expects($this->once())
            ->method('getCount')
            ->willReturn(2);
        $this->storeManagerMock->expects($this->never())
            ->method('getWebsite');

        $this->assertEquals($data, $this->dataProcessor->prepareEntityData($data));
    }
}
