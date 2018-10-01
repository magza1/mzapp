<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Brand;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Model\Config;
use Aheadworks\ShopByBrand\Model\Locale\LanguageResolver;
use Aheadworks\ShopByBrand\Model\Url;
use Magento\Framework\View\Result\Page;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Block\Html\Pager;

/**
 * Class PageConfig
 * @package Aheadworks\ShopByBrand\Model\Brand
 */
class PageConfig
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var LanguageResolver
     */
    private $languageResolver;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Config $config
     * @param Url $url
     * @param LanguageResolver $languageResolver
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Config $config,
        Url $url,
        LanguageResolver $languageResolver,
        StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->url = $url;
        $this->languageResolver = $languageResolver;
        $this->storeManager = $storeManager;
    }

    /**
     * Apply brand options to result page
     *
     * @param Page $page
     * @param BrandInterface $brand
     * @return Page
     */
    public function apply(Page $page, BrandInterface $brand)
    {
        $pageConfig = $page->getConfig();
        $pageConfig->getTitle()->set(__($brand->getName()));
        $pageConfig->setMetadata('description', $brand->getMetaDescription());
        $pageConfig->addRemotePageAsset(
            $this->url->getBrandUrl($brand),
            'canonical',
            ['attributes' => ['rel' => 'canonical']]
        );
        if ($this->config->isAddStoreCodeToUrlsEnabled()) {
            $websiteId = $this->storeManager->getWebsite()->getId();
            foreach ($this->storeManager->getStores() as $store) {
                if ($store->getWebsiteId() == $websiteId) {
                    $storeId = $store->getId();
                    $language = $this->languageResolver->get($storeId);
                    $pageConfig->addRemotePageAsset(
                        $this->url->getBrandUrl($brand, $storeId),
                        'unknown',
                        ['attributes' => ['rel' => 'alternate', 'hreflang' => $language]],
                        'hreflang-' . $language
                    );
                }
            }
        }
        if ($this->config->isAddNoindexToPaginationPages() && $this->isPaginationPage($page)) {
            $pageConfig->setMetadata('robots', 'NOINDEX,FOLLOW');
        }
        return $page;
    }

    /**
     * Check if pagination page
     *
     * @param Page $page
     * @return bool
     */
    private function isPaginationPage(Page $page)
    {
        /** @var Pager $pager */
        $pager = $page->getLayout()->getBlock('product_list_toolbar_pager');
        return $pager && $pager->getCurrentPage() > 1;
    }
}
