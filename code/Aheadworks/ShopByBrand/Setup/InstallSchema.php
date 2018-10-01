<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\ShopByBrand\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 * @package Aheadworks\ShopByBrand\Setup
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        /**
         * Create table 'aw_sbb_brand'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_sbb_brand'))
            ->addColumn(
                'brand_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Brand Id'
            )->addColumn(
                'attribute_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Attribute ID'
            )->addColumn(
                'option_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Option Id'
            )->addColumn(
                'url_key',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'URL-Key'
            )->addColumn(
                'logo',
                Table::TYPE_TEXT,
                255,
                [],
                'Logo'
            )->addColumn(
                'is_featured',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => '0'],
                'Is Featured'
            )->addIndex(
                $installer->getIdxName('aw_sbb_brand', ['url_key']),
                ['url_key']
            )->addForeignKey(
                $installer->getFkName(
                    'aw_sbb_brand',
                    'attribute_id',
                    'eav_attribute',
                    'attribute_id'
                ),
                'attribute_id',
                $installer->getTable('eav_attribute'),
                'attribute_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(
                    'aw_sbb_brand',
                    'option_id',
                    'eav_attribute_option',
                    'option_id'
                ),
                'option_id',
                $installer->getTable('eav_attribute_option'),
                'option_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Brand');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_sbb_brand_content'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_sbb_brand_content'))
            ->addColumn(
                'brand_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Brand Id'
            )->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Store Id'
            )->addColumn(
                'meta_title',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Meta Title'
            )->addColumn(
                'meta_description',
                Table::TYPE_TEXT,
                '2M',
                ['nullable' => false],
                'Meta Description'
            )->addColumn(
                'description',
                Table::TYPE_TEXT,
                '2M',
                [],
                'Description'
            )->addIndex(
                $installer->getIdxName('aw_sbb_brand_content', ['brand_id']),
                ['brand_id']
            )->addIndex(
                $installer->getIdxName('aw_sbb_brand_content', ['store_id']),
                ['store_id']
            )->addForeignKey(
                $installer->getFkName(
                    'aw_sbb_brand_content',
                    'brand_id',
                    'aw_sbb_brand',
                    'brand_id'
                ),
                'brand_id',
                $installer->getTable('aw_sbb_brand'),
                'brand_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(
                    'aw_sbb_brand_content',
                    'store_id',
                    'store',
                    'store_id'
                ),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )->setComment('Brand Content');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_sbb_brand_website'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_sbb_brand_website'))
            ->addColumn(
                'brand_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Brand Id'
            )->addColumn(
                'website_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Website Id'
            )->addIndex(
                $installer->getIdxName('aw_sbb_brand_website', ['brand_id']),
                ['brand_id']
            )->addIndex(
                $installer->getIdxName('aw_sbb_brand_website', ['website_id']),
                ['website_id']
            )->addForeignKey(
                $installer->getFkName(
                    'aw_sbb_brand_website',
                    'brand_id',
                    'aw_sbb_brand',
                    'brand_id'
                ),
                'brand_id',
                $installer->getTable('aw_sbb_brand'),
                'brand_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(
                    'aw_sbb_brand_website',
                    'website_id',
                    'store_website',
                    'website_id'
                ),
                'website_id',
                $installer->getTable('store_website'),
                'website_id',
                Table::ACTION_CASCADE
            )->setComment('Brand To Website Relation Table');
        $installer->getConnection()->createTable($table);
    }
}
