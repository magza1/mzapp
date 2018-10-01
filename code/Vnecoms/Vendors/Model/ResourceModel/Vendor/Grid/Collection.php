<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\Vendors\Model\ResourceModel\Vendor\Grid;

use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Psr\Log\LoggerInterface as Logger;

/**
 * App page collection
 */
class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager
    ) {
        $mainTable = 'ves_vendor_entity';
        $resourceModel = 'Vnecoms\Vendors\Model\ResourceModel\Vendor';
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }
    
    /**
     * Init collection select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
//         $this->join(['order_grid'=>$this->getTable('sales_order_grid')], 'main_table.order_id=order_grid.entity_id',array('increment_id','store_id','store_name','base_currency_code','order_currency_code','shipping_name','billing_name','billing_address','shipping_address','shipping_and_handling','total_refunded','customer_name'));
        //$this->getSelect()->join($this->getTable('sales_order_grid'), 'main_table.order_id=sales_order_grid.entity_id',array('store_id','store_name','base_currency_code','order_currency_code','shipping_name','billing_name','billing_address','shipping_address','shipping_and_handling','total_refunded'));
        return $this;
    }
}
