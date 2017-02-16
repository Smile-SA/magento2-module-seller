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
namespace Smile\Seller\Ui\Component\Seller\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\EavValidationRules;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Smile\Seller\Model\Locator\LocatorInterface;

/**
 * Seller Data provider for adminhtml edit form
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var mixed
     */
    private $collectionFactory;

    /**
     * @var \Smile\Seller\Model\Locator\LocatorInterface
     */
    private $locator;

    /**
     * @var \Magento\Ui\DataProvider\Modifier\PoolInterface
     */
    private $pool;

    /**
     * @param string           $name              DataProvider name.
     * @param string           $primaryFieldName  Database primary key field.
     * @param string           $requestFieldName  Request identifier field.
     * @param mixed            $collectionFactory Item collection factory.
     * @param PoolInterface    $pool              Modifiers Pool
     * @param LocatorInterface $locator           Locator Interface
     * @param array            $meta              Default meta.
     * @param array            $data              Default data.
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        $collectionFactory,
        PoolInterface $pool,
        LocatorInterface $locator,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->collectionFactory = $collectionFactory;
        $this->pool = $pool;
        $this->locator = $locator;
        $this->meta = $this->prepareMeta($meta);
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        $data = parent::getData();

        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $data = $modifier->modifyData($data);
        }

        return $data;
    }

    /**
     * {@inheritDoc}
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
     *
     * @param array $meta The meta data.
     *
     * @return array
     */
    private function prepareMeta($meta)
    {
        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }
}
