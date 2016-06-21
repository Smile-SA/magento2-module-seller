<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\Seller
 * @author    Aurelien FOUCRET <aurelien.foucret@smile.fr>
 * @copyright 2016 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\Seller\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Seller Schema install class.
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var array The attributes backend tables definitions.
     */
    private $backendTypes = [
        'datetime' => ['value', \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME, null, [], 'Value'],
        'decimal'  => ['value', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '12,4', [], 'Value'],
        'int'      => ['value', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, [], 'Value'],
        'text'     => ['value', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k', [], 'Value'],
        'varchar'  => ['value', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '255', [], 'Value'],
    ];

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->createSellerEntityTable($setup);
        $this->createAttributesTables($setup);
        $setup->endSetup();
    }

    /**
     * Process the Seller's EAV table creation
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup The Setup
     *
     * @throws \Zend_Db_Exception
     */
    private function createSellerEntityTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()
            ->newTable($setup->getTable('smile_seller_entity'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'attribute_set_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Attriute Set ID'
            )
            ->addColumn(
                'seller_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                64,
                [],
                'Seller code'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Creation Time'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Update Time'
            )
            ->addIndex($setup->getIdxName('smile_seller_entity', ['seller_code']), ['seller_code'])
            ->setComment('Smile Seller Table');

        $setup->getConnection()->createTable($table);
    }

    /**
     * Process the Seller's EAV Attributes tables creation
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup The Setup
     *
     * @throws \Zend_Db_Exception
     */
    private function createAttributesTables(SchemaSetupInterface $setup)
    {
        foreach ($this->backendTypes as $backendType => $valueFieldProperties) {
            $backendTableName = 'smile_seller_entity_' . $backendType;
            $table = $setup->getConnection()
                ->newTable($setup->getTable($backendTableName))
                ->addColumn(
                    'value_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true],
                    'Value ID'
                )
                ->addColumn(
                    'attribute_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Attribute ID'
                )
                ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Store ID'
                )
                ->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Entity ID'
                );

            call_user_func_array([$table, 'addColumn'], $valueFieldProperties);

            $table->addIndex(
                $setup->getIdxName(
                    $backendTableName,
                    ['entity_id', 'attribute_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['entity_id', 'attribute_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex($setup->getIdxName($backendTableName, ['entity_id']), ['entity_id'])
            ->addIndex($setup->getIdxName($backendTableName, ['attribute_id']), ['attribute_id'])
            ->addIndex($setup->getIdxName($backendTableName, ['store_id']), ['store_id'])
            ->addForeignKey(
                $setup->getFkName($backendTableName, 'attribute_id', 'eav_attribute', 'attribute_id'),
                'attribute_id',
                $setup->getTable('eav_attribute'),
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName($backendTableName, 'entity_id', 'smile_seller_entity_', 'entity_id'),
                'entity_id',
                $setup->getTable('smile_seller_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName($backendTableName, 'store_id', 'store', 'store_id'),
                'store_id',
                $setup->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Smile Seller ' . ucfirst($backendType) . 'Attribute Backend Table');

            $setup->getConnection()->createTable($table);
        }
    }
}
