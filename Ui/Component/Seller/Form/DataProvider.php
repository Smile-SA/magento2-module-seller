<?php

declare(strict_types=1);

namespace Smile\Seller\Ui\Component\Seller\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Smile\Seller\Model\Locator\LocatorInterface;

/**
 * Seller Data provider for adminhtml edit form.
 */
class DataProvider extends AbstractDataProvider
{
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        private mixed $collectionFactory,
        private PoolInterface $pool,
        private LocatorInterface $locator,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->meta = $this->prepareMeta($meta);
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        $data = parent::getData();

        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $data = $modifier->modifyData($data);
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function getCollection()
    {
        if ($this->collection === null) {
            $this->collection = $this->collectionFactory->create();
            $this->collection->addAttributeToSelect('*');
            if ($this->locator->getStore()) {
                $this->collection->setStoreId($this->locator->getStore()->getId());
            }
        }

        return $this->collection;
    }

    /**
     * Prepare meta data.
     */
    private function prepareMeta(array $meta): array
    {
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }
}
