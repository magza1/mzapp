<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\Credit\Ui\DataProvider\Credit;

use Vnecoms\Credit\Model\ResourceModel\Credit\Transaction\CollectionFactory;

/**
 * Class ProductDataProvider
 */
class TransactionDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    /**
     * Product collection
     *
     * @var \Vnecoms\Credit\Model\ResourceModel\Credit\Transaction\Collection
     */
    protected $collection;


    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Ui\DataProvider\AddFieldToCollectionInterface[] $addFieldStrategies
     * @param \Magento\Ui\DataProvider\AddFilterToCollectionInterface[] $addFilterStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->collection->join($this->collection->getTable('customer_grid_flat'), "entity_id=customer_id",['email'=>'email','name'=>'name'],null,'left');
    }
    
    /**
     * Add field to select
     *
     * @param string|array $field
     * @param string|null $alias
     * @return void
     */
    public function addField($field, $alias = null)
    {
        return $this;
    }
}
