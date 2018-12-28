<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Plugin;

use Magento\CatalogSearch\Model\Adapter\Aggregation\Checker\Query\CatalogView;
use Magento\Framework\App\RequestInterface;

/**
 * Class CatalogViewPlugin
 * @package Aheadworks\ShopByBrand\Model\Plugin\Adapter\Aggregation\Checker\Query
 */
class CatalogViewPlugin
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * @param CatalogView $subject
     * @param bool $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsApplicable($subject, $result)
    {
        if ($this->request->getFullActionName() == 'aw_sbb_brand_view' && !$result) {
            return true;
        }

        return $result;
    }
}
