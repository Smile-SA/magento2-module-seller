<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\Seller
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2016 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\Seller\Ui\Component\Seller\Listing;

use Magento\Framework\Api\Filter;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;
use Smile\Seller\Model\ResourceModel\Seller\Collection;

/**
 * Data Provider for UI components based on Sellers
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * Seller collection
     *
     * @var Collection
     */
    protected $collection;

    /**
     * @var AddFieldToCollectionInterface[]
     */
    protected array $addFieldStrategies;

    /**
     * @var AddFilterToCollectionInterface[]
     */
    protected array $addFilterStrategies;

    /**
     * Construct
     *
     * @param string                                                    $name                Component name
     * @param string                                                    $primaryFieldName    Primary field Name
     * @param string                                                    $requestFieldName    Request field name
     * @param CollectionFactory                                         $collectionFactory   The collection factory
     * @param AddFieldToCollectionInterface[]  $addFieldStrategies  Add field Strategy
     * @param AddFilterToCollectionInterface[] $addFilterStrategies Add filter Strategy
     * @param array                                                     $meta                Component Meta
     * @param array                                                     $data                Component extra data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        $collectionFactory,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->collection = $collectionFactory->create();

        $this->addFieldStrategies  = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData(): array
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }
        $items = $this->getCollection()->toArray();

        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items'        => array_values($items),
        ];
    }

    /**
     * Add field to select
     *
     * @param string|array $field The field
     * @param string|null  $alias Alias for the field
     *
     * @return void
     */
    public function addField($field, $alias = null): void
    {
        if (isset($this->addFieldStrategies[$field])) {
            $this->addFieldStrategies[$field]->addField($this->getCollection(), $field, $alias);

            return ;
        }
        parent::addField($field, $alias);
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(Filter $filter): void
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
