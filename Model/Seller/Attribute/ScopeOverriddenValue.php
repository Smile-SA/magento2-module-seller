<?php

declare(strict_types=1);

namespace Smile\Seller\Model\Seller\Attribute;

use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\UnionExpression;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Store;
use Smile\Seller\Api\Data\SellerAttributeInterface;
use Smile\Seller\Api\Data\SellerInterface;
use Smile\Seller\Model\Seller\Attribute\Repository as AttributeRepository;

/**
 * Scope Overridden value finder for Seller entities.
 */
class ScopeOverriddenValue
{
    /**
     * @var ?array
     */
    private ?array $attributesValues;

    private AdapterInterface $resourceConnection;

    public function __construct(
        private AttributeRepository $attributeRepository,
        private SearchCriteriaBuilder $searchCriteriaBuilder,
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection->getConnection();
    }

    /**
     * Whether attribute value is overridden in specific store.
     *
     * @throws LocalizedException
     */
    public function containsValue(SellerInterface $entity, string $attributeCode, int $storeId): bool
    {
        if ((int) $storeId === Store::DEFAULT_STORE_ID) {
            return false;
        }
        if ($this->attributesValues === null) {
            $this->initAttributeValues($entity, (int) $storeId);
        }

        return isset($this->attributesValues[$storeId])
        && array_key_exists($attributeCode, $this->attributesValues[$storeId]);
    }

    /**
     * Init Attributes Values.
     *
     * @throws LocalizedException
     */
    private function initAttributeValues(SellerInterface $entity, int $storeId): void
    {
        $attributeTables = [];

        /** @var AbstractAttribute $attribute */
        foreach ($this->getScopedAttributes() as $attribute) {
            if (!$attribute->isStatic()) {
                $attributeTables[$attribute->getBackend()->getTable()][] = $attribute->getAttributeId();
            }
        }

        $storeIds = [Store::DEFAULT_STORE_ID];
        if ($storeId !== Store::DEFAULT_STORE_ID) {
            $storeIds[] = $storeId;
        }

        $selects = [];
        foreach ($attributeTables as $attributeTable => $attributeCodes) {
            $select = $this->resourceConnection->select()
                ->from(['t' => $attributeTable], ['value' => 't.value', 'store_id' => 't.store_id'])
                ->join(
                    ['a' => $this->resourceConnection->getTableName('eav_attribute')],
                    'a.attribute_id = t.attribute_id',
                    ['attribute_code' => 'a.attribute_code']
                )
                ->where('entity_id = ?', $entity->getId())
                ->where('t.attribute_id IN (?)', $attributeCodes)
                ->where('t.store_id IN (?)', $storeIds);
            $selects[] = $select;
        }

        $unionSelect = new UnionExpression($selects, Select::SQL_UNION_ALL);

        $attributes = $this->resourceConnection->fetchAll((string) $unionSelect);
        foreach ($attributes as $attribute) {
            $this->attributesValues[$attribute['store_id']][$attribute['attribute_code']] = $attribute['value'];
        }
    }

    /**
     * Retrieve a list of attributes that can be scoped by store.
     *
     * @return AttributeInterface[]
     */
    private function getScopedAttributes(): array
    {
        $searchResult = $this->attributeRepository->getList(
            $this->searchCriteriaBuilder->addFilters([])->create()
        );

        return array_filter($searchResult->getItems(), function ($item) {
            /** @var SellerAttributeInterface $item */
            return !$item->isScopeGlobal();
        });
    }
}
