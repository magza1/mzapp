<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Layer\DataSource;

use Magento\Framework\App\RequestInterface;

/**
 * Class ConfigProvider
 * @package Aheadworks\ShopByBrand\Model\Layer\DataSource
 */
class ConfigProvider
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Get data source config
     *
     * @return array
     */
    public function getConfig()
    {
        $brandId = $this->request->getParam('brand_id');
        return $brandId ? ['brand_id' => $brandId] : [];
    }
}
