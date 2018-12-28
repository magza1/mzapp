<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsProduct\Model\Adapter\Mysql\Aggregation;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Model\Stock;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\App\ObjectManager;

class DataProvider extends \Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider
{

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @param Config $eavConfig
     * @param ResourceConnection $resource
     * @param ScopeResolverInterface $scopeResolver
     * @param Session $customerSession
     */
    public function __construct(
        Config $eavConfig,
        ResourceConnection $resource,
        ScopeResolverInterface $scopeResolver,
        Session $customerSession
    ) {
        $this->eavConfig = $eavConfig;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->scopeResolver = $scopeResolver;
        $this->customerSession = $customerSession;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataSet(
        BucketInterface $bucket,
        array $dimensions,
        Table $entityIdsTable
    ) {

        $currentScope = $this->scopeResolver->getScope($dimensions['scope']->getValue())->getId();
        

        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, $bucket->getField());

        $select = $this->getSelect();

        $select->joinInner(
            ['entities' => $entityIdsTable->getName()],
            'main_table.entity_id  = entities.entity_id',
            []
        );



        $approvalValue = '('.$this->getAllowState(true).')';
        $select->join(
                ['at_approval'=>$this->resource->getTableName('catalog_product_entity_int')],
                "at_approval.entity_id = entities.entity_id AND at_approval.attribute_id = '".$this->getIdOfAttributeCode('catalog_product','approval')."'"
                ." AND at_approval.value IN ".$approvalValue." AND at_approval.store_id = '0'", //@todo dont know why need to 0
                []
        );
        $vendorIds = $this->getAllowVendorId(true);
        if (!empty($vendorIds)) {
            $select->join(
                ['product_entity'=>$this->resource->getTableName('catalog_product_entity')],
                "product_entity.entity_id = entities.entity_id AND product_entity.vendor_id NOT IN (".$vendorIds.")",
                []
            );
        }


        if ($attribute->getAttributeCode() === 'price') {
            /** @var \Magento\Store\Model\Store $store */
            $store = $this->scopeResolver->getScope($currentScope);
            if (!$store instanceof \Magento\Store\Model\Store) {
                throw new \RuntimeException('Illegal scope resolved');
            }
            $table = $this->resource->getTableName('catalog_product_index_price');
            $select->from(['main_table' => $table], null)
                ->columns([BucketInterface::FIELD_VALUE => 'main_table.min_price'])
                ->where('main_table.customer_group_id = ?', $this->customerSession->getCustomerGroupId())
                ->where('main_table.website_id = ?', $store->getWebsiteId());
        } else {
            $currentScopeId = $this->scopeResolver->getScope($currentScope)
                ->getId();
            $table = $this->resource->getTableName(
                'catalog_product_index_eav' . ($attribute->getBackendType() === 'decimal' ? '_decimal' : '')
            );
            $subSelect = $select;
            $subSelect->from(['main_table' => $table], ['main_table.entity_id', 'main_table.value'])
                ->distinct()
                ->joinLeft(
                    ['stock_index' => $this->resource->getTableName('cataloginventory_stock_status')],
                    'main_table.entity_id = stock_index.product_id',
                    []
                )
                ->where('main_table.attribute_id = ?', $attribute->getAttributeId())
                ->where('main_table.store_id = ? ', $currentScopeId)
                ->where('stock_index.stock_status = ?', Stock::STOCK_IN_STOCK);
            $parentSelect = $this->getSelect();
            $parentSelect->from(['main_table' => $subSelect], ['main_table.value']);
            $select = $parentSelect;
        }

        return $select;

    }

    /**
     * {@inheritdoc}
     */
    public function execute(Select $select)
    {
        return $this->connection->fetchAssoc($select);
    }

    /**
     * @return Select
     */
    private function getSelect()
    {
        return $this->connection->select();
    }

    public function getIdOfAttributeCode($entityCode, $code)
    {
        return \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magento\Eav\Model\ResourceModel\Entity\Attribute')
            ->getIdByCode($entityCode,$code);
    }

    protected function getAllowState($reFormat=false)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $allowState = $om->create('Vnecoms\VendorsProduct\Helper\Data')->getAllowedApprovalStatus();

        if($reFormat) {
            return implode($allowState, ', ');
        }

        return $allowState;
    } 

    protected function getAllowVendorId($reFormat=false)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $allowState = $om->create('Vnecoms\Vendors\Helper\Data')->getNotActiveVendorIds();

        if($reFormat) {
            return implode($allowState, ', ');
        }

        return $allowState;
    } 
}