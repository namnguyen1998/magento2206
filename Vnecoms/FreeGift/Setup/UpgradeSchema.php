<?php
namespace Vnecoms\FreeGift\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            /**
             * Create table 'ves_freegift_salesrule'
             */
            $table = $installer->getConnection()->newTable(
                $installer->getTable('ves_freegift_salesrule')
            )->addColumn(
                'rule_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Rule ID'
            )->addColumn(
                'website_ids',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Website Ids'
            )->addColumn(
                'group_ids',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Group Ids'
            )->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Rule Name'
            )->addColumn(
                'description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Description'
            )->addColumn(
                'from_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                ['nullable' => true],
                'From Date'
            )->addColumn(
                'to_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                ['nullable' => true],
                'To Date'
            )->addColumn(
                'conditions_serialized',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '1M',
                [],
                'Rule Conditions'
            )->addColumn(
                'product_ids',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '1M',
                [],
                'Free Product Ids'
            )->addColumn(
                'stop_rules_processing',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 1],
                'Stop Rules Processing'
            )->addColumn(
                'sort_order',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Sort Order'
            )->addColumn(
                'is_active',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Status'
            );
            
            $installer->getConnection()->createTable($table);
        }

	    if (version_compare($context->getVersion(), '2.0.3', '<')) {
            $installer->getConnection()->addColumn(
                $setup->getTable('ves_freegift_salesrule'),
                'no_of_freegift',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'after' => 'product_ids',
                    'comment' => 'Number of product allow customer to select'
                ]
            );
        }

        if (version_compare($context->getVersion(), '2.0.4', '<')) {
            $installer->getConnection()->addColumn(
                $setup->getTable('ves_freegift_salesrule'),
                'uses_per_customer',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0',
                    'after' => 'to_date',
                    'comment' => 'Uses Per Customer'
                ]
            );

            if (!$installer->tableExists('ves_freegift_salesrule_customer')) {
                $table = $installer->getConnection()->newTable(
                    $installer->getTable('ves_freegift_salesrule_customer')
                )->addColumn(
                    'rule_customer_id', 
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 
                    null, 
                    [
                        'identity' => true, 
                        'nullable' => false, 
                        'primary' => true, 
                        'unsigned' => true
                    ],
                    'Rule Customer Id'
                )->addColumn(
                    'rule_id', 
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 
                    null, 
                    [
                        'nullable' => false, 
                        'unsigned' => true
                    ], 
                    'Rule Id'
                )->addColumn(
                    'customer_id', 
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 
                    null, 
                    [
                        'nullable' => false, 
                        'unsigned' => true
                    ], 
                    'Customer Id'
                )->addColumn(
                    'times_used',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true, 
                        'default' => '0'
                    ],
                    'Times Used'
                )->setComment('Ves Freegift Salesrule Customer');

                $installer->getConnection()->createTable($table);
            }
        }
        
        $installer->endSetup();
    }
}
