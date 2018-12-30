<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Observer;

use Aheadworks\ShopByBrand\Model\Sitemap;
use Aheadworks\ShopByBrand\Model\Sitemap\ItemsProvider;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class AddSitemapItemsObserver
 * @package Aheadworks\ShopByBrand\Observer
 */
class AddSitemapItemsObserver implements ObserverInterface
{
    /**
     * @var ItemsProvider
     */
    private $itemsProvider;

    /**
     * @param ItemsProvider $itemsProvider
     */
    public function __construct(ItemsProvider $itemsProvider)
    {
        $this->itemsProvider = $itemsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        /** @var Sitemap $sitemap */
        $sitemap = $event->getObject();
        $sitemap->appendSitemapItem(
            $this->itemsProvider->getBrandItems($sitemap->getStoreId())
        );
    }
}
