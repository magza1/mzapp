<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Vnecoms\VendorsMessage\Setup;

use Magento\Customer\Model\Customer;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $customerSetupFactory;



    /**
     * Init
     *
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(\Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory)
    {
        $this->customerSetupFactory = $customerSetupFactory;
    }
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            $customerSetup->addAttribute(
                Customer::ENTITY,
                'is_block_user',
                [
                    'label' => 'Is Block User',
                    'type' => 'static',
                    'input' => 'text',
                    'position' => 145,
                    'visible' => false,
                    'default' => '',
                    'visible' => false,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '0',
                    'visible_on_front' => false,
                ]
            );
            $setup->endSetup();
        }
    }
}
