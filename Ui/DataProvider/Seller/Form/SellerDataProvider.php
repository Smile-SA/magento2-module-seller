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
namespace Smile\Seller\Ui\DataProvider\Seller\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

/**
 * Seller Data provider for adminhtml edit form
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class SellerDataProvider  extends AbstractDataProvider
{
    /**
     * @var PoolInterface
     */
    private $pool;

    /**
     * @param string            $name              Component name
     * @param string            $primaryFieldName  Primary field Name
     * @param string            $requestFieldName  Request field Name
     * @param CollectionFactory $collectionFactory Collection Factory
     * @param PoolInterface     $pool              Pool Interface
     * @param array             $meta              Component Meta
     * @param array             $data              Component Data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        $collectionFactory,
        PoolInterface $pool,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->collection = $collectionFactory->create();
        $this->pool = $pool;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $this->data = $modifier->modifyData($this->data);
        }

        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta()
    {
        $meta = parent::getMeta();

        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }
}
