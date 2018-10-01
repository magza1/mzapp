<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Test\Unit\Model\Brand\PostDataProcessor;

use Aheadworks\ShopByBrand\Model\Brand\PostDataProcessor\BrandId;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\ShopByBrand\Model\Brand\PostDataProcessor\BrandId
 */
class BrandIdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BrandId
     */
    private $dataProcessor;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->dataProcessor = $objectManager->getObject(BrandId::class);
    }

    public function testPrepareEntityData()
    {
        $data = ['field' => 'value'];
        $expectedResult = [
            'field' => 'value',
            'brand_id' => null
        ];

        $this->assertEquals($expectedResult, $this->dataProcessor->prepareEntityData($data));
    }
}
