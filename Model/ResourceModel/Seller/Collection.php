<?php

declare(strict_types=1);

namespace Smile\Seller\Model\ResourceModel\Seller;

use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Eav\Model\EntityFactory as EavEntityFactory;
use Magento\Eav\Model\ResourceModel\Helper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\Data\CollectionDataSourceInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Smile\Seller\Model\ResourceModel\Seller as SellerResource;
use Smile\Seller\Model\Seller;

/**
 * Sellers Collection.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) The parent class had already too much coupling
 */
class Collection extends AbstractCollection implements CollectionDataSourceInterface
{
    protected string $_eventPrefix = 'smile_seller_entity_collection';
    protected string $_eventObject = 'smile_seller_entity_collection';
    protected ?int $sellerAttributeSetId = null;
    protected ?string $sellerAttributeSetName = null;
    private ?int $storeId = null;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        Config $eavConfig,
        ResourceConnection $resource,
        EavEntityFactory $eavEntityFactory,
        Helper $resourceHelper,
        private StoreManagerInterface $storeManager,
        UniversalFactory $universalFactory,
        ?AdapterInterface $connection = null,
        ?string $attributeSetName = null
    ) {
        $this->sellerAttributeSetName = $attributeSetName;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $connection
        );
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(Seller::class, SellerResource::class);

        if ($this->sellerAttributeSetId == null) {
            if ($this->sellerAttributeSetName !== null) {
                /** @var SellerResource $resource */
                $resource = $this->getResource();
                $this->sellerAttributeSetId = $resource
                    ->getAttributeSetIdByName($this->sellerAttributeSetName);
            }
        }
    }

    /**
     * Set store scope.
     */
    public function setStore(Store $store): self
    {
        $this->setStoreId($this->storeManager->getStore($store)->getId());

        return $this;
    }

    /**
     * Set store scope.
     */
    public function setStoreId(mixed $storeId): self
    {
        if ($storeId instanceof Store) {
            $storeId = $storeId->getId();
        }
        $this->storeId = (int) $storeId;

        return $this;
    }

    /**
     * Return current store id.
     */
    public function getStoreId(): int
    {
        if ($this->storeId === null) {
            $this->setStoreId($this->storeManager->getStore()->getId());
        }

        return $this->storeId;
    }

    /**
     * Retrieve default store id.
     */
    public function getDefaultStoreId(): int
    {
        return Store::DEFAULT_STORE_ID;
    }

    /**
     * @inheritdoc
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        // Retrieve only sellers of current attribute set if specified.
        if ($this->sellerAttributeSetId !== null) {
            $this->addFieldToFilter('attribute_set_id', (int) $this->sellerAttributeSetId);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _getLoadAttributesSelect($table, $attributeIds = [])
    {
        if (empty($attributeIds)) {
            $attributeIds = $this->_selectAttributes;
        }

        $storeId = $this->getStoreId();
        $connection = $this->getConnection();

        $entityIdField = $this->getEntityPkName($this->getEntity());

        $select = $this->getBaseAttributesSelect($table, $attributeIds);

        $storeCondition = $this->getDefaultStoreId();

        if ($storeId) {
            $joinCondition = [
                't_s.attribute_id = t_d.attribute_id',
                "t_s.{$entityIdField} = t_d.{$entityIdField}",
                $connection->quoteInto('t_s.store_id = ?', $storeId),
            ];

            $select->joinLeft(['t_s' => $table], implode(' AND ', $joinCondition), []);

            $storeCondition = $connection->getIfNullSql('t_s.store_id', Store::DEFAULT_STORE_ID);
        }

        $select->where('t_d.store_id = ?', $storeCondition);

        return $select;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.ElseExpression) cf. \Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection
     */
    protected function _joinAttributeToSelect($method, $attribute, $tableAlias, $condition, $fieldCode, $fieldAlias)
    {
        $storeId = $this->getStoreId();
        if (isset($this->_joinAttributes[$fieldCode]['store_id'])) {
            $storeId = $this->_joinAttributes[$fieldCode]['store_id'];
        }

        $connection = $this->getConnection();

        if ($storeId != $this->getDefaultStoreId() && !$attribute->isScopeGlobal()) {
            /**
             * Add joining default value for not default store
             * if value for store is null - we use default value
             */
            $defCondition = '(' . implode(') AND (', $condition) . ')';
            $defAlias = $tableAlias . '_default';
            $defAlias = $this->getConnection()->getTableName($defAlias);
            $defFieldAlias = str_replace($tableAlias, $defAlias, $fieldAlias);
            $tableAlias = $this->getConnection()->getTableName($tableAlias);

            $defCondition = str_replace($tableAlias, $defAlias, $defCondition);
            $defCondition .= $connection->quoteInto(
                // @phpstan-ignore-next-line as $alias null value is default
                ' AND ' . $connection->quoteColumnAs("{$defAlias}.store_id", null) . " = ?",
                $this->getDefaultStoreId()
            );

            $this->getSelect()->{$method}(
                [$defAlias => $attribute->getBackend()->getTable()],
                $defCondition,
                []
            );

            $method = 'joinLeft';
            $fieldAlias = $this->getConnection()->getCheckSql(
                "{$tableAlias}.value_id > 0",
                $fieldAlias,
                $defFieldAlias
            );
            $this->_joinAttributes[$fieldCode]['condition_alias'] = $fieldAlias;
            $this->_joinAttributes[$fieldCode]['attribute'] = $attribute;
        } else {
            $storeId = $this->getDefaultStoreId();
        }

        $condition[] = $connection->quoteInto(
            // @phpstan-ignore-next-line as $alias null value is default
            $connection->quoteColumnAs("{$tableAlias}.store_id", null) . ' = ?',
            $storeId
        );

        return parent::_joinAttributeToSelect($method, $attribute, $tableAlias, $condition, $fieldCode, $fieldAlias);
    }

    /**
     * Retrieve Base select for attributes of this collection.
     *
     * @throws LocalizedException
     */
    private function getBaseAttributesSelect(string $table, array $attributeIds = []): Select
    {
        $connection    = $this->getConnection();
        $entityTable   = $this->getEntity()->getEntityTable();

        $entityIdField = $this->getEntityPkName($this->getEntity());

        return $connection->select()->from(
            ['t_d' => $table],
            ['attribute_id']
        )->join(
            ['e' => $entityTable],
            "e.{$entityIdField} = t_d.{$entityIdField}",
            ['e.entity_id']
        )->where(
            "e.entity_id IN (?)",
            array_keys($this->_itemsById)
        )->where(
            't_d.attribute_id IN (?)',
            $attributeIds
        );
    }
}
