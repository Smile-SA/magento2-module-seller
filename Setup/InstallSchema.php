<?php

namespace Smile\Seller\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

/**
 * Seller Schema install class.
 */
class InstallSchema implements InstallSchemaInterface
{
    private array $backendTypes = [
        'datetime' => ['value', Table::TYPE_DATETIME, null, [], 'Value'],
        'decimal' => ['value', Table::TYPE_DECIMAL, '12,4', [], 'Value'],
        'int' => ['value', Table::TYPE_INTEGER, null, [], 'Value'],
        'text' => ['value', Table::TYPE_TEXT, '64k', [], 'Value'],
        'varchar' => ['value', Table::TYPE_TEXT, '255', [], 'Value'],
    ];

    /**
     * @inheritdoc
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->createSellerEntityTable($setup);
        $this->createAttributesTables($setup);
        $setup->endSetup();
    }

    /**
     * Process the Seller's EAV table creation.
     *
     * @throws Zend_Db_Exception
     */
    private function createSellerEntityTable(SchemaSetupInterface $setup): void
    {
        $table = $setup->getConnection()
            ->newTable($setup->getTable('smile_seller_entity'))
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'attribute_set_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Attriute Set ID'
            )
            ->addColumn(
                'seller_code',
                Table::TYPE_TEXT,
                64,
                [],
                'Seller code'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Creation Time'
            )
            ->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Update Time'
            )
            ->addIndex($setup->getIdxName('smile_seller_entity', ['seller_code']), ['seller_code'])
            ->setComment('Smile Seller Table');

        $setup->getConnection()->createTable($table);
    }

    /**
     * Process the Seller's EAV Attributes tables creation.
     *
     * @throws Zend_Db_Exception
     */
    private function createAttributesTables(SchemaSetupInterface $setup): void
    {
        foreach ($this->backendTypes as $backendType => $valueFieldProperties) {
            $backendTableName = 'smile_seller_entity_' . $backendType;
            $table = $setup->getConnection()
                ->newTable($setup->getTable($backendTableName))
                ->addColumn(
                    'value_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true],
                    'Value ID'
                )
                ->addColumn(
                    'attribute_id',
                    Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Attribute ID'
                )
                ->addColumn(
                    'store_id',
                    Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Store ID'
                )
                ->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Entity ID'
                );

            call_user_func_array([$table, 'addColumn'], $valueFieldProperties);

            $table->addIndex(
                $setup->getIdxName(
                    $backendTableName,
                    ['entity_id', 'attribute_id', 'store_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['entity_id', 'attribute_id', 'store_id'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex($setup->getIdxName($backendTableName, ['entity_id']), ['entity_id'])
            ->addIndex($setup->getIdxName($backendTableName, ['attribute_id']), ['attribute_id'])
            ->addIndex($setup->getIdxName($backendTableName, ['store_id']), ['store_id'])
            ->addForeignKey(
                $setup->getFkName($backendTableName, 'attribute_id', 'eav_attribute', 'attribute_id'),
                'attribute_id',
                $setup->getTable('eav_attribute'),
                'attribute_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName($backendTableName, 'entity_id', 'smile_seller_entity_', 'entity_id'),
                'entity_id',
                $setup->getTable('smile_seller_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName($backendTableName, 'store_id', 'store', 'store_id'),
                'store_id',
                $setup->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Smile Seller ' . ucfirst($backendType) . 'Attribute Backend Table');

            $setup->getConnection()->createTable($table);
        }
    }
}
