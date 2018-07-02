<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\Seller
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2017 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\Seller\Model\Seller\Attribute;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\UnionExpression;
use Magento\Store\Model\Store;
use Smile\Seller\Model\Seller\Attribute\Repository as AttributeRepository;

/**
 * Scope Overridden value finder for Seller entities.
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class ScopeOverriddenValue
{
    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var array
     */
    private $attributesValues;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $resourceConnection;

    /**
     * ScopeOverriddenValue constructor.
     *
     * @param AttributeRepository   $attributeRepository   Attribute Repository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder Search Criteria builder
     * @param FilterBuilder         $filterBuilder         Filter Builder
     * @param ResourceConnection    $resourceConnection    Resource Connection
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        ResourceConnection $resourceConnection
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->resourceConnection = $resourceConnection->getConnection();
    }

    /**
     * Whether attribute value is overridden in specific store
     *
     * @param \Smile\Seller\Api\Data\SellerInterface $entity        The seller
     * @param string                                 $attributeCode The attribute code
     * @param int                                    $storeId       The Store Id
     *
     * @return bool
     */
    public function containsValue($entity, $attributeCode, $storeId)
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
     * Init Attributes Values
     *
     * @param \Smile\Seller\Api\Data\SellerInterface $entity  The seller
     * @param int                                    $storeId The Store Id
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    private function initAttributeValues($entity, $storeId)
    {
        /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
        $attributeTables = [];

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
     * @return \Magento\Eav\Api\Data\AttributeInterface[]
     */
    private function getScopedAttributes()
    {
        $searchResult = $this->attributeRepository->getList(
            $this->searchCriteriaBuilder->addFilters([])->create()
        );

        return array_filter($searchResult->getItems(), function ($item) {
            return method_exists($item, 'isScopeGlobal') ? !$item->isScopeGlobal() : false;
        });
    }
}
