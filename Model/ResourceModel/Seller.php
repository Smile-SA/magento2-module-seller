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

namespace Smile\Seller\Model\ResourceModel;

use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Eav\Model\Entity\Context;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\DataObject;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\StoreManagerInterface;
use Smile\Seller\Api\Data\SellerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */

/**
 * Seller Resource Model
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
class Seller extends AbstractEntity
{
    /**
     * Id of 'is_active' seller attribute
     *
     * @var ?int
     */
    protected ?int $isActiveAttributeId = null;

    /**
     * Store id
     *
     * @var ?int
     */
    protected ?int $storeId = null;

    /**
     * Core event manager proxy
     *
     * @var ?ManagerInterface
     */
    protected ?ManagerInterface $eventManager = null;

    /**
     * @var ?StoreManagerInterface
     */
    protected ?StoreManagerInterface $storeManager = null;

    /**
     * @var EntityManager
     */
    protected EntityManager $entityManager;

    /**
     * Seller constructor.
     *
     * @param Context                   $context       Entity Context
     * @param StoreManagerInterface     $storeManager  Store Manager
     * @param ManagerInterface          $eventManager  Event Manager
     * @param EntityManager             $entityManager Entity Manager
     * @param array                     $data          Seller data
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ManagerInterface $eventManager,
        EntityManager $entityManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storeManager  = $storeManager;
        $this->eventManager  = $eventManager;
        $this->entityManager = $entityManager;
    }

    /**
     * Entity type getter and lazy loader
     *
     * @return Type
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getEntityType(): Type
    {
        if (empty($this->_type)) {
            $this->setType(SellerInterface::ENTITY);
        }

        return parent::getEntityType();
    }

    /**
     * Set store Id
     *
     * @param integer $storeId The store Id
     *
     * @return $this
     */
    public function setStoreId(int $storeId): self
    {
        $this->storeId = $storeId;

        return $this;
    }

    /**
     * Return store id
     *
     * @return integer
     */
    public function getStoreId(): int
    {
        if ($this->storeId === null) {
            return $this->storeManager->getStore()->getId();
        }

        return $this->storeId;
    }

    /**
     * Check if seller id exist
     *
     * @param int $entityId The Seller Id
     *
     * @return string
     */
    public function checkId(int $entityId): string
    {
        $select = $this->getConnection()->select()->from(
            $this->getEntityTable(),
            'entity_id'
        )->where(
            'entity_id = :entity_id'
        );
        $bind = ['entity_id' => $entityId];

        return $this->getConnection()->fetchOne($select, $bind);
    }

    /**
     * Check array of seller identifiers
     *
     * @param array $ids The seller ids
     *
     * @return array
     */
    public function verifyIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $select = $this->getConnection()->select()->from(
            $this->getEntityTable(),
            'entity_id'
        )->where(
            'entity_id IN(?)',
            $ids
        );

        return $this->getConnection()->fetchCol($select);
    }

    /**
     * Get "is_active" attribute identifier
     *
     * @return int
     */
    public function getIsActiveAttributeId(): int
    {
        if ($this->isActiveAttributeId === null) {
            $this->isActiveAttributeId = (int) $this->_eavConfig
                ->getAttribute($this->getEntityType(), 'is_active')
                ->getAttributeId();
        }

        return $this->isActiveAttributeId;
    }


    /**
     * Reset firstly loaded attributes
     *
     * @param DataObject    $object     Object being loaded
     * @param integer       $entityId   The entity Id
     * @param array|null    $attributes The attributes
     *
     * @return $this
     */
    public function load($object, $entityId, $attributes = []): self
    {
        $this->_attributes = [];
        $this->loadAttributesMetadata($attributes);

        $object = $this->entityManager->load($object, $entityId);

        if (!$this->entityManager->has($object)) {
            $object->isObjectNew(true);
        }

        $this->afterLoad($object);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($object): self
    {
        $this->beforeDelete($object);
        $this->entityManager->delete($object);
        $this->afterDelete($object);

        return $this;
    }

    /**
     * Save entity's attributes into the object's resource
     *
     * @param AbstractModel $object The Object
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function save(AbstractModel $object): self
    {
        $this->beforeSave($object);
        $this->entityManager->save($object);
        $this->afterSave($object);

        return $this;
    }

    /**
     * Retrieve Attribute set data by id or name
     *
     * @param ?string $attributeSetId The attribute Set Id or Name
     *
     * @return string
     */
    public function getAttributeSetIdByName(?string $attributeSetId): string
    {
        $select = $this->_resource->getConnection()->select();
        $field  = 'attribute_set_name';
        $table  = $this->_resource->getTableName("eav_attribute_set");

        $select->from($table, "attribute_set_id")
            ->where($this->getConnection()->prepareSqlCondition("entity_type_id", ['eq' => $this->getTypeId()]))
            ->where($this->getConnection()->prepareSqlCondition($field, ['eq' => $attributeSetId]));

        return $this->_resource->getConnection()->fetchOne($select);
    }

    /**
     * Before Saving a Seller.
     * Enforce loading of all attributes to ensure their beforeSave is correctly processed.
     *
     * @param DataObject $object The object (seller) being saved.
     */
    public function beforeSave(DataObject $object): void
    {
        $this->loadAllAttributes($object);
        parent::beforeSave($object);
    }

    /**
     * Get Seller identifier by code
     *
     * @param string $code The Seller Code
     *
     * @return int|false
     */
    public function getIdByCode(string $code): int|false
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from($this->getEntityTable(), 'entity_id')->where('seller_code = :seller_code');

        $bind = [':seller_code' => (string) $code];

        return $connection->fetchOne($select, $bind);
    }
}
