<?php
/**
* Copyright 2018 aheadWorks. All rights reserved. 
*  See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Model\ResourceModel\Brand;

use Aheadworks\ShopByBrand\Api\Data\BrandInterface;
use Aheadworks\ShopByBrand\Model\Brand;
use Aheadworks\ShopByBrand\Model\ResourceModel\Brand as BrandResource;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\Store;

/**
 * Class Collection
 * @package Aheadworks\ShopByBrand\Model\ResourceModel\Brand
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = 'brand_id';

    /**
     * @var int
     */
    private $storeId;

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(Brand::class, BrandResource::class);
        $this->_map['fields']['brand_id'] = 'main_table.brand_id';
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $connection = $this->getResource()->getConnection();
        $select = $this->getSelect();
        $select->joinLeft(
            ['default_option_value' => $this->getTable('eav_attribute_option_value')],
            implode(
                ' AND ',
                [
                    'default_option_value.option_id = main_table.option_id',
                    'default_option_value.store_id = ' . Store::DEFAULT_STORE_ID
                ]
            ),
            ['default_option_value.value']
        );
        if ($this->storeId) {
            $select->columns(
                ['name' => $connection->getIfNullSql('option_value.value', 'default_option_value.value')]
            )->joinLeft(
                ['option_value' => $this->getTable('eav_attribute_option_value')],
                implode(
                    ' AND ',
                    [
                        'option_value.option_id = main_table.option_id',
                        'option_value.store_id = ' . $this->storeId
                    ]
                ),
                ['option_value.value']
            );
            $this->_map['fields']['name'] = $connection->getCheckSql(
                'ISNULL(option_value.value)',
                'default_option_value.value',
                'option_value.value'
            );
        } else {
            $select->columns(['name' => 'default_option_value.value']);
            $this->_map['fields']['name'] = 'default_option_value.value';
        }
        $select->join(
            ['attribute_table' => $this->getTable('eav_attribute')],
            'attribute_table.attribute_id = main_table.attribute_id',
            ['attribute_code' => 'attribute_table.attribute_code']
        );
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSelectCountSql()
    {
        return parent::getSelectCountSql()
            ->resetJoinLeft();
    }

    /**
     * Set store ID
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == BrandInterface::WEBSITE_IDS) {
            return $this->addWebsiteFilter($condition);
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Add website filter
     *
     * @param int $websiteId
     * @return $this
     */
    public function addWebsiteFilter($websiteId)
    {
        $this->addFilter('website_linkage_table.website_id', ['eq' => $websiteId], 'public');
        return $this;
    }

    /**
     * Join to website linkage table if website filter is applied
     *
     * @return void
     */
    private function joinWebsiteLinkageTable()
    {
        if ($this->getFilter('website_linkage_table.website_id')) {
            $select = $this->getSelect();
            $select->joinLeft(
                ['website_linkage_table' => $this->getTable('aw_sbb_brand_website')],
                'main_table.brand_id = website_linkage_table.brand_id',
                []
            )->group('main_table.brand_id');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        $this->attachWebsites();
        $this->attachContent();
        return parent::_afterLoad();
    }

    /**
     * {@inheritdoc}
     */
    protected function _renderFiltersBefore()
    {
        $this->joinWebsiteLinkageTable();
        parent::_renderFiltersBefore();
    }

    /**
     * Attach website IDs to collection's items
     *
     * @return void
     */
    private function attachWebsites()
    {
        $ids = $this->getAllIds();
        if (count($ids)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(['website_linkage_table' => $this->getTable('aw_sbb_brand_website')])
                ->where('website_linkage_table.brand_id IN (?)', $ids);
            /** @var Brand $item */
            foreach ($this as $item) {
                $websiteIds = [];
                $id = $item->getBrandId();
                foreach ($connection->fetchAll($select) as $data) {
                    if ($data['brand_id'] == $id) {
                        $websiteIds[] = $data['website_id'];
                    }
                }
                $item->setWebsiteIds($websiteIds);
            }
        }
    }

    /**
     * Attach content data to collection's items
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function attachContent()
    {
        $ids = $this->getAllIds();
        if (count($ids)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(['content_table' => $this->getTable('aw_sbb_brand_content')])
                ->where('content_table.brand_id IN (?)', $ids);
            /** @var Brand $item */
            foreach ($this as $item) {
                $content = [];
                $id = $item->getBrandId();
                $metaTitle = null;
                $metaDescription = null;
                $description = null;
                foreach ($connection->fetchAll($select) as $data) {
                    if ($data['brand_id'] == $id) {
                        $content[] = $data;
                        if ($this->storeId && $this->storeId == $data['store_id']) {
                            list($metaTitle, $metaDescription, $description) = [
                                $data['meta_title'],
                                $data['meta_description'],
                                $data['description']
                            ];
                        }
                        if ($data['store_id'] == Store::DEFAULT_STORE_ID) {
                            if (!$this->storeId) {
                                list($metaTitle, $metaDescription, $description) = [
                                    $data['meta_title'],
                                    $data['meta_description'],
                                    $data['description']
                                ];
                            }
                            if (!$metaTitle) {
                                $metaTitle = $data['meta_title'];
                            }
                            if (!$metaDescription) {
                                $metaDescription = $data['meta_description'];
                            }
                            if (!$description) {
                                $description = $data['description'];
                            }
                        }
                    }
                }
                $item
                    ->setContent($content)
                    ->setMetaTitle($metaTitle)
                    ->setMetaDescription($metaDescription)
                    ->setDescription($description);
            }
        }
    }
}
