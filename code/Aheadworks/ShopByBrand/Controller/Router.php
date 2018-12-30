<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Controller;

use Aheadworks\ShopByBrand\Model\ResourceModel\Brand as BrandResource;
use Aheadworks\ShopByBrand\Model\Config;
use Aheadworks\ShopByBrand\Model\Url;
use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RouterInterface;

/**
 * Class Router
 * @package Aheadworks\ShopByBrand\Controller
 */
class Router implements RouterInterface
{
    /**
     * @var ActionFactory
     */
    private $actionFactory;

    /**
     * @var BrandResource
     */
    private $brandResource;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param ActionFactory $actionFactory
     * @param BrandResource $brandResource
     * @param Config $config
     */
    public function __construct(
        ActionFactory $actionFactory,
        BrandResource $brandResource,
        Config $config
    ) {
        $this->actionFactory = $actionFactory;
        $this->brandResource = $brandResource;
        $this->config = $config;
    }

    /**
     * Match brand pages
     *
     * @param RequestInterface|Http $request
     * @return bool
     */
    public function match(RequestInterface $request)
    {
        $parts = explode('/', trim($request->getPathInfo(), '/'));
        if (array_shift($parts) != Url::ROUTE_TO_BRAND) {
            return false;
        }
        $urlKeyWithSuffix = array_shift($parts);
        if (!$urlKeyWithSuffix) {
            return false;
        }
        $suffix = $this->config->getBrandUrlSuffix();
        if (!empty($suffix)) {
            $urlKey = preg_replace('/' . $suffix .'$/', '', $urlKeyWithSuffix);
        } else {
            $urlKey = $urlKeyWithSuffix;
        }

        try {
            $brandId = $this->brandResource->getBrandIdByUrlKey($urlKey);
        } catch (\Exception $exception) {
            $brandId = null;
        }
        if (!$brandId) {
            return false;
        }

        $request
            ->setAlias(
                \Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS,
                ltrim($request->getOriginalPathInfo(), '/')
            )
            ->setModuleName('aw_sbb')
            ->setControllerName('brand')
            ->setActionName('view')
            ->setParams(['brand_id' => $brandId]);

        return $this->actionFactory->create(Forward::class);
    }
}
