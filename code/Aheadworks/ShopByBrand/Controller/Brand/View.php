<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Controller\Brand;

use Aheadworks\ShopByBrand\Api\BrandRepositoryInterface;
use Aheadworks\ShopByBrand\Model\Brand\PageConfig;
use Aheadworks\ShopByBrand\Model\Config;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\Page;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class View
 * @package Aheadworks\ShopByBrand\Controller\Brand
 */
class View extends Action
{
    /**
     * @var BrandRepositoryInterface
     */
    private $brandRepository;

    /**
     * @var LayerResolver
     */
    private $layerResolver;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var PageConfig
     */
    private $brandPageConfig;

    /**
     * @param Context $context
     * @param BrandRepositoryInterface $brandRepository
     * @param LayerResolver $layerResolver
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     * @param PageConfig $brandPageConfig
     */
    public function __construct(
        Context $context,
        BrandRepositoryInterface $brandRepository,
        LayerResolver $layerResolver,
        StoreManagerInterface $storeManager,
        Config $config,
        PageConfig $brandPageConfig
    ) {
        parent::__construct($context);
        $this->brandRepository = $brandRepository;
        $this->layerResolver = $layerResolver;
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->brandPageConfig = $brandPageConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $brand = $this->brandRepository->get(
                $this->getRequest()->getParam('brand_id')
            );

            $websiteId = $this->storeManager->getWebsite()->getId();
            if ($brand->getAttributeCode() != $this->config->getBrandProductAttributeCode()
                || !in_array($websiteId, $brand->getWebsiteIds())
            ) {
                /** @var Forward $resultForward */
                $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
                return $resultForward
                    ->setModule('cms')
                    ->setController('noroute')
                    ->forward('index');
            }

            $this->layerResolver->create('aw_brand');

            /** @var Page $resultPage */
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            return $this->brandPageConfig->apply($resultPage, $brand);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
    }
}
