<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vnecoms\VendorsCredit\Setup;

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
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $setup->getConnection() ->addColumn(
                $setup->getTable('ves_vendor_withdrawal'),
                'reason_cancel',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => \Magento\Framework\DB\Ddl\Table::MAX_TEXT_SIZE,
                    'nullable' => false,
                    'after' => 'status',
                    'comment' => 'Reason'
                ]
            );

            $setup->getConnection() ->addColumn(
                $setup->getTable('ves_vendor_withdrawal'),
                'code_of_transfer',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' =>  \Magento\Framework\DB\Ddl\Table::MAX_TEXT_SIZE,
                    'nullable' => false,
                    'after' => 'status',
                    'comment' => 'Code of Transfer'
                ]
            );
        }

        $setup->endSetup();
    }
}
