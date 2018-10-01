<?php
namespace Vnecoms\Vendors\Ui\DataProvider\Vendor;

use Vnecoms\Vendors\Model\ResourceModel\Vendor\CollectionFactory;

/**
 * Class ProductDataProvider
 */
class AccountDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    /**
     * account collection
     *
     * @var \Vnecoms\Vendors\Model\ResourceModel\Vendor\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;
    
    /**
     *
     * @param unknown $name
     * @param unknown $primaryFieldName
     * @param unknown $requestFieldName
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param CollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->_eventManager = $eventManager;
        $this->collection = $collectionFactory->create();
        $this->_eventManager->dispatch('vnecoms_vendors_ui_accountdataprovider_collection_prepare', ['collection' => $this->collection]);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }
        $vendors = $this->getCollection()->toArray();

        return [
            'totalRecords' => sizeof($vendors),
            'items' => array_values($vendors),
        ];
    }
}
