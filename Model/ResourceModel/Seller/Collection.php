<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\Seller
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2016 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\Seller\Model\ResourceModel\Seller;

use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Eav\Model\EntityFactory as EavEntityFactory;
use Magento\Eav\Model\ResourceModel\Helper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Sellers Collection
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName) The properties are inherited
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Collection extends AbstractCollection
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'smile_seller_entity_collection';

    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject = 'smile_seller_entity_collection';

    /**
     * @var null Attribute set id of the entity
     */
    private $sellerAttributeSetId = null;

    /**
     * @var null Attribute set name of the entity
     */
    private $sellerAttributeSetName = null;

    /**
     * Current scope (store Id)
     *
     * @var integer
     */
    private $storeId;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Collection constructor.
     *
     * @param \Magento\Framework\Data\Collection\EntityFactory             $entityFactory    Entity Factory
     * @param \Psr\Log\LoggerInterface                                     $logger           Logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy    Fetch Strategy
     * @param \Magento\Framework\Event\ManagerInterface                    $eventManager     Event Manager
     * @param \Magento\Eav\Model\Config                                    $eavConfig        EAV Config
     * @param \Magento\Framework\App\ResourceConnection                    $resource         Resource Connection
     * @param \Magento\Eav\Model\EntityFactory                             $eavEntityFactory EAV Entity Factory
     * @param \Magento\Eav\Model\ResourceModel\Helper                      $resourceHelper   Resource Helper
     * @param \Magento\Store\Model\StoreManagerInterface                   $storeManager     The Store Manager
     * @param \Magento\Framework\Validator\UniversalFactory                $universalFactory Universal Factory
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null          $connection       Database Connection
     * @param null                                                         $attributeSetName Seller Attribute Set Name
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
        StoreManagerInterface $storeManager,
        UniversalFactory $universalFactory,
        AdapterInterface $connection = null,
        $attributeSetName = null
    ) {
        $this->sellerAttributeSetName = $attributeSetName;
        $this->storeManager           = $storeManager;

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
     * Set store scope
     *
     * @param int|string|\Magento\Store\Model\Store $store The store
     *
     * @return $this
     */
    public function setStore($store)
    {
        $this->setStoreId($this->storeManager->getStore($store)->getId());

        return $this;
    }

    /**
     * Set store scope
     *
     * @param int|string|\Magento\Store\Model\Store $storeId The store Id or Store
     *
     * @return $this
     */
    public function setStoreId($storeId)
    {
        if ($storeId instanceof \Magento\Store\Model\Store) {
            $storeId = $storeId->getId();
        }
        $this->storeId = (int) $storeId;

        return $this;
    }

    /**
     * Return current store id
     *
     * @return int
     */
    public function getStoreId()
    {
        if ($this->storeId === null) {
            $this->setStoreId($this->storeManager->getStore()->getId());
        }

        return $this->storeId;
    }

    /**
     * Retrieve default store id
     *
     * @return int
     */
    public function getDefaultStoreId()
    {
        return \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }

    /**
     * Init collection and determine table names
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName) The method is inherited
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Smile\Seller\Model\Seller', 'Smile\Seller\Model\ResourceModel\Seller');
        if ($this->sellerAttributeSetId == null) {
            if ($this->sellerAttributeSetName !== null) {
                $this->sellerAttributeSetId = $this->getResource()->getAttributeSetIdByName($this->sellerAttributeSetName);
            }
        }
    }

    /**
     * Init select. Retrieve only sellers of current attribute set if specified.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName) The method is inherited
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        if ($this->sellerAttributeSetId !== null) {
            $this->addFieldToFilter('attribute_set_id', (int) $this->sellerAttributeSetId);
        }

        return $this;
    }

    /**
     * Retrieve attributes load select
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName) The method is inherited
     *
     * @param string    $table        The table to load attributes from
     * @param array|int $attributeIds The attribute ids to load
     *
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    protected function _getLoadAttributesSelect($table, $attributeIds = [])
    {
        if (empty($attributeIds)) {
            $attributeIds = $this->_selectAttributes;
        }

        $storeId    = $this->getStoreId();
        $connection = $this->getConnection();

        $entityIdField = $this->getEntityPkName($this->getEntity());

        $select = $this->getBaseSelect($table, $attributeIds);

        $storeCondition = $this->getDefaultStoreId();

        if ($storeId) {
            $joinCondition = [
                't_s.attribute_id = t_d.attribute_id',
                "t_s.{$entityIdField} = t_d.{$entityIdField}",
                $connection->quoteInto('t_s.store_id = ?', $storeId),
            ];

            $select->joinLeft(['t_s' => $table], implode(' AND ', $joinCondition), []);

            $storeCondition = $connection->getIfNullSql('t_s.store_id', \Magento\Store\Model\Store::DEFAULT_STORE_ID);
        }

        $select->where('t_d.store_id = ?', $storeCondition);

        return $select;
    }

    /**
     * Retrieve Base select for this collection.
     *
     * @param string $table        The attribute table
     * @param array  $attributeIds The attribute ids
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return \Magento\Framework\Db\Select
     */
    private function getBaseSelect($table, $attributeIds = [])
    {
        $connection    = $this->getConnection();
        $entityTable   = $this->getEntity()->getEntityTable();

        $entityIdField = $this->getEntityPkName($this->getEntity());

        $select = $connection->select()->from(
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

        return $select;
    }
}
