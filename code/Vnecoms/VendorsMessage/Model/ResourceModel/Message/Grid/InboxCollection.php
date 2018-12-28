<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Model\ResourceModel\Message\Grid;

use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Psr\Log\LoggerInterface as Logger;

/**
 * App page collection
 */
class InboxCollection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager
    ) {
        $mainTable = 'ves_vendor_message';
        $resourceModel = 'Vnecoms\VendorsMessage\Model\ResourceModel\Message';
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }
    
    protected function _construct()
    {
        parent::_construct();

        $this->addFilterToMap(
            'status',
            'main_table.status'
        );
        $this->addFilterToMap(
            'created_at',
            'msg_detail.created_at'
        );
    }
    
    /**
     * Init collection select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addFieldToFilter('is_deleted', 0);
        $this->addFieldToFilter('is_inbox', 1);
        $this->getSelect()->joinLeft(
            ['msg_detail' => $this->getTable('ves_vendor_message_detail')],
            'main_table.message_id = msg_detail.message_id',
            ['*','msg_count' => 'count(msg_detail.detail_id)']
        );
        $this->getSelect()->group('msg_detail.message_id');
        $this->_joinedTables['msg_detail'] = true;
    }
}
