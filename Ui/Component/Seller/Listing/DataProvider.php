<?php

namespace Smile\Seller\Ui\Component\Seller\Listing;

use Magento\Framework\Api\Filter;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Smile\Retailer\Model\ResourceModel\Retailer\CollectionFactory as RetailerCollectionFactory;
use Smile\Seller\Model\ResourceModel\Seller\CollectionFactory as SellerCollectionFactory;

/**
 * Data Provider for UI components based on Sellers.
 */
class DataProvider extends AbstractDataProvider
{
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        SellerCollectionFactory|RetailerCollectionFactory $collectionFactory,
        protected array $addFieldStrategies = [],
        protected array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }

    /**
     * Get data.
     */
    public function getData(): array
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }
        $items = $this->getCollection()->toArray();

        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => array_values($items),
        ];
    }

    /**
     * @inheritdoc
     */
    public function addField($field, $alias = null)
    {
        if (isset($this->addFieldStrategies[$field])) {
            $this->addFieldStrategies[$field]->addField($this->getCollection(), $field, $alias);

            return ;
        }
        parent::addField($field, $alias);
    }

    /**
     * @inheritdoc
     */
    public function addFilter(Filter $filter)
    {
        if (isset($this->addFilterStrategies[$filter->getField()])) {
            $this->addFilterStrategies[$filter->getField()]
                ->addFilter(
                    $this->getCollection(),
                    $filter->getField(),
                    [$filter->getConditionType() => $filter->getValue()]
                );
        }
        if (!isset($this->addFilterStrategies[$filter->getField()])) {
            parent::addFilter($filter);
        }
    }
}
