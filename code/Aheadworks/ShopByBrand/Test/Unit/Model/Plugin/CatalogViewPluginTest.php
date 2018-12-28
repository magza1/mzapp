<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Observer;

use Aheadworks\ShopByBrand\Model\Plugin\CatalogViewPlugin;
use Magento\CatalogSearch\Model\Adapter\Aggregation\Checker\Query\CatalogView;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Aheadworks\ShopByBrand\Model\Plugin\CatalogViewPlugin
 */
class CatalogViewPluginTest extends TestCase
{
    /**
     * @var CatalogViewPlugin
     */
    private $plugin;

    /**
     * @var CatalogView|\PHPUnit_Framework_MockObject_MockObject $subject
     */
    private $subjectMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject $subject
     */
    private $requestMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->subjectMock = $this->getMockBuilder(CatalogView::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->setMethods(['getFullActionName'])
            ->getMockForAbstractClass();

        $this->plugin = $objectManager->getObject(
            CatalogViewPlugin::class,
            [
                'request' => $this->requestMock
            ]
        );
    }

    /**
     * Test afterIsApplicable method
     *
     * @param string $fullActionName
     * @param bool $result
     * @param bool $expectedResult
     * @dataProvider afterIsApplicableDataProvider
     */
    public function testAfterIsApplicable($fullActionName, $result, $expectedResult)
    {
        $this->requestMock->expects($this->once())
            ->method('getFullActionName')
            ->willReturn($fullActionName);

        $this->assertEquals($expectedResult, $this->plugin->afterIsApplicable($this->subjectMock, $result));
    }

    /**
     * @return array
     */
    public function afterIsApplicableDataProvider()
    {
        return [
            'category view page' => ['catalog_category_view', true, true],
            'catalogsearch result page' => ['catalogsearch_result_index', true, true],
            'sbb brand page' => ['aw_sbb_brand_view', false, true],
        ];
    }
}
