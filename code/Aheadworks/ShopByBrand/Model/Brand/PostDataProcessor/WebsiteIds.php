<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\Brand\PostDataProcessor;

use Aheadworks\ShopByBrand\Model\Brand\PostDataProcessorInterface;
use Magento\Store\Api\WebsiteManagementInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class WebsiteIds
 * @package Aheadworks\ShopByBrand\Model\Brand\PostDataProcessor
 */
class WebsiteIds implements PostDataProcessorInterface
{
    /**
     * @var WebsiteManagementInterface
     */
    private $websiteManagement;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param WebsiteManagementInterface $websiteManagement
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        WebsiteManagementInterface $websiteManagement,
        StoreManagerInterface $storeManager
    ) {
        $this->websiteManagement = $websiteManagement;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareEntityData($data)
    {
        if ($this->websiteManagement->getCount() == 1) {
            $data['website_ids'] = [
                $this->storeManager->getWebsite()->getId()
            ];
        }
        return $data;
    }
}
