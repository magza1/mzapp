<?php

namespace Vnecoms\VendorsMessage\Setup;

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
         * Create table 'ves_store_credit'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ves_vendor_message')
        )->addColumn(
            'message_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn(
            'identifier',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Identifier'
        )->addColumn(
            'owner_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [ 'unsigned' => true, 'nullable' => false,],
            'Owner Id'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            1,
            ['unsigned' => true, 'nullable' => false,],
            'Status (0 => Draft, 1 => Unread, 2 => Read, 3 => Sent)'
        )->addColumn(
            'is_inbox',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            1,
            ['unsigned' => true, 'nullable' => false,],
            'Is an inbox message'
        )->addColumn(
            'is_outbox',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            1,
            ['unsigned' => true, 'nullable' => false,],
            'Is an outbox message'
        )->addColumn(
            'is_deleted',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            1,
            ['unsigned' => true, 'nullable' => false,],
            'Is deleted Message (0 => No, 1 => Yes)'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->setComment(
            'Vendor Message Table'
        );
        $installer->getConnection()->createTable($table);
        
        /**
         * Create table 'ves_vendor_review_detail'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ves_vendor_message_detail')
        )->addColumn(
            'detail_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Detail Id'
        )->addColumn(
            'message_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Message Id'
        )->addColumn(
            'sender_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Sender Id'
        )->addColumn(
            'sender_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Sender Email'
        )->addColumn(
            'sender_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Sender Name'
        )->addColumn(
            'receiver_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Receiver Id'
        )->addColumn(
            'receiver_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Receiver Email'
        )->addColumn(
            'receiver_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Receiver Name'
        )->addColumn(
            'subject',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Subject'
        )->addColumn(
            'content',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            \Magento\Framework\DB\Ddl\Table::MAX_TEXT_SIZE,
            [],
            'Message Content'
        )->addColumn(
            'is_read',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            1,
            ['unsigned' => true, 'nullable' => false,],
            'Is read Message (0 => No, 1 => Yes)'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addForeignKey(
            $installer->getFkName('ves_vendor_message_detail', 'message_id', 'ves_vendor_message', 'message_id'),
            'message_id',
            $installer->getTable('ves_vendor_message'),
            'message_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Vendor Message Detail Table'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();

    }
}
