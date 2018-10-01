<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Vnecoms\VendorsGroup\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
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
        $installer->startSetup();

        /**
         * Create table 'customer_address_entity'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ves_vendor_group_config')
        )->addColumn(
            'config_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Config Id'
        )->addColumn(
            'group_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            5,
            ['nullable' => false,'unsigned' => true],
            'Group Id'
        )->addColumn(
            'resource_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Resource Id'
        )
        ->addColumn(
            'value',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            \Magento\Framework\DB\Ddl\Table::MAX_TEXT_SIZE,
            [],
            'Value'
        )->addForeignKey(
            $installer->getFkName('ves_vendor_group_config', 'group_id', 'ves_vendor_group', 'vendor_group_id'),
            'group_id',
            $installer->getTable('ves_vendor_group'),
            'vendor_group_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Advanced Group Rules'
        );
        $installer->getConnection()->createTable($table);        
        
        $installer->endSetup();

    }
}
