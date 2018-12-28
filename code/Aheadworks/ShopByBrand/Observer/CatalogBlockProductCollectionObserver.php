<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\Product\ProductList\Toolbar;

/**
 * Class CatalogBlockProductCollectionObserver
 * @package Aheadworks\ShopByBrand\Observer
 */
class CatalogBlockProductCollectionObserver implements ObserverInterface
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
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        $productCollection = $observer->getEvent()->getCollection();
        if ($productCollection instanceof \Magento\Framework\Data\Collection) {
            $select = $productCollection->getSelect();
            if ($this->canSortByPosition($select->getPart(Select::FROM))) {
                $dir = $this->request->getParam(Toolbar::DIRECTION_PARAM_NAME, Select::SQL_ASC);
                $select->order('position_in_brand ' . strtoupper($dir));
            }
        }

        return $this;
    }

    /**
     * Check can sort by brand position
     *
     * @param array $part
     * @return bool
     */
    private function canSortByPosition($part)
    {
        $sortOrder = $this->request->getParam(Toolbar::ORDER_PARAM_NAME, null);
        if (array_key_exists('additional_brand_products', $part)
            && (!$sortOrder || $sortOrder == 'position')
        ) {
            return true;
        }
        return false;
    }
}
