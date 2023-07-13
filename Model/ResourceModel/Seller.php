<?php

declare(strict_types=1);

namespace Smile\Seller\Model\ResourceModel;

use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Eav\Model\Entity\Context;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\DataObject;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\StoreManagerInterface;
use Smile\Seller\Api\Data\SellerInterface;

/**
 * Seller Resource Model.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Seller extends AbstractEntity
{
    protected array $_attributes = [];
    protected ?int $isActiveAttributeId = null;
    protected ?int $storeId = null;

    public function __construct(
        Context $context,
        protected StoreManagerInterface $storeManager,
        protected EntityManager $entityManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Entity type getter and lazy loader.
     *
     * @throws LocalizedException
     */
    public function getEntityType(): Type
    {
        // @phpstan-ignore-next-line as like inherit method
        if (empty($this->_type)) {
            $this->setType(SellerInterface::ENTITY);
        }

        return parent::getEntityType();
    }

    /**
     * Set store Id.
     */
    public function setStoreId(int $storeId): self
    {
        $this->storeId = $storeId;

        return $this;
    }

    /**
     * Return store id.
     */
    public function getStoreId(): int
    {
        if ($this->storeId === null) {
            return $this->storeManager->getStore()->getId();
        }

        return $this->storeId;
    }

    /**
     * Check if seller id exist.
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
     * Check array of seller identifiers.
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
     * Get "is_active" attribute identifier.
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
     * @inheritdoc
     */
    public function load($object, $entityId, $attributes = [])
    {
        // Reset firstly loaded attributes
        $this->_attributes = [];
        $this->loadAttributesMetadata($attributes);

        $object = $this->entityManager->load($object, (string) $entityId);

        if (!$this->entityManager->has($object)) {
            $object->isObjectNew(true);
        }

        $this->afterLoad($object);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function delete($object)
    {
        $this->beforeDelete($object);
        $this->entityManager->delete($object);
        $this->afterDelete($object);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function save(AbstractModel $object)
    {
        $this->beforeSave($object);
        $this->entityManager->save($object);
        $this->afterSave($object);

        return $this;
    }

    /**
     * Retrieve Attribute set data by id or name.
     */
    public function getAttributeSetIdByName(?string $attributeSetId): int
    {
        $select = $this->_resource->getConnection()->select();
        $field  = 'attribute_set_name';
        $table  = $this->_resource->getTableName("eav_attribute_set");

        $select->from($table, "attribute_set_id")
            ->where($this->getConnection()->prepareSqlCondition("entity_type_id", ['eq' => $this->getTypeId()]))
            ->where($this->getConnection()->prepareSqlCondition($field, ['eq' => $attributeSetId]));

        return (int) $this->_resource->getConnection()->fetchOne($select);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave(DataObject $object): void
    {
        // Enforce loading of all attributes to ensure their beforeSave is correctly processed.
        $this->loadAllAttributes($object);
        parent::beforeSave($object);
    }

    /**
     * Get Seller identifier by code.
     */
    public function getIdByCode(string $code): int
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getEntityTable(), 'entity_id')
            ->where('seller_code = :seller_code');

        return (int) $connection->fetchOne($select, [':seller_code' => $code]);
    }
}
