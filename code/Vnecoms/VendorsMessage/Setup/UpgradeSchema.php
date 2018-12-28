<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsMessage\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade the Catalog module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.2', '<')) {

            $table = $installer->getConnection()
                ->newTable($installer->getTable('ves_vendor_message_block'))
                ->addColumn(
                    'block_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Block ID'
                )
                ->addColumn(
                    'owner_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'unsigned' => true],
                    'Owner Id'
                )
                ->addForeignKey(
                    $installer->getFkName('ves_vendor_message_block', 'owner_id', 'customer_entity', 'entity_id'),
                    'owner_id',
                    $installer->getTable('customer_entity'),
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->setComment('Vendors Message Block User');
            $installer->getConnection()->createTable($table);


            $table = $installer->getConnection()
                ->newTable($installer->getTable('ves_vendor_message_warning'))
                ->addColumn(
                    'warning_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Warning ID'
                )
                ->addColumn(
                    'detail_message_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Message Detaitl '
                )
                ->addColumn(
                    'message_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'unsigned' => true],
                    'Message Id'
                )
                ->addForeignKey(
                    $installer->getFkName('ves_vendor_message_warning', 'message_id', 'ves_vendor_message', 'message_id'),
                    'message_id',
                    $installer->getTable('ves_vendor_message'),
                    'message_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->setComment('Vendors Message Warning');
            $installer->getConnection()->createTable($table);

            $table = $installer->getConnection()
                ->newTable($installer->getTable('ves_vendor_message_pattern'))
                ->addColumn(
                    'pattern_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Pattern ID'
                )
                ->addColumn(
                    'pattern',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '64k',
                    ['nullable' => false, 'default' => ''],
                    'Pattern'
                )
                ->addColumn(
                    'message',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '2M',
                    ['nullable' => false, 'default' => ''],
                    'Message'
                )
                ->addColumn(
                    'action',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'action'
                )
                ->addColumn(
                    'status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Status'
                )
                ->setComment('Vendors Message Spam Pattern');
            $installer->getConnection()->createTable($table);
            $setup->endSetup();
        }

    }
}
