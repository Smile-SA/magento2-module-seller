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
namespace Smile\Seller\Ui\DataProvider\Seller\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Smile\Seller\Model\Locator\LocatorInterface;
use Magento\Ui\Component\Form;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * General Modifier for Sellers
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class General extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $modelId = $this->locator->getSeller()->getId();

        if (!isset($data[$modelId][static::DATA_SOURCE_DEFAULT][ProductAttributeInterface::CODE_STATUS])) {
            $data[$modelId][static::DATA_SOURCE_DEFAULT][ProductAttributeInterface::CODE_STATUS] = '1';
        }

        return $data;
    }


    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $meta = $this->prepareFirstPanel($meta);
        $meta = $this->customizeStatusField($meta);

        return $meta;
    }

    /**
     * Disable collapsible and set empty label
     *
     * @param array $meta
     * @return array
     */
    protected function prepareFirstPanel(array $meta)
    {
        if ($generalPanelCode = $this->getFirstPanelCode($meta)) {
            $meta[$generalPanelCode] = $this->arrayManager->merge(
                'arguments/data/config',
                $meta[$generalPanelCode],
                [
                    'label' => '',
                    'collapsible' => false,
                ]
            );
        }

        return $meta;
    }

    /**
     * Customize Status field
     *
     * @param array $meta
     * @return array
     */
    protected function customizeStatusField(array $meta)
    {
        $switcherConfig = [
            'dataType' => Form\Element\DataType\Number::NAME,
            'formElement' => Form\Element\Checkbox::NAME,
            'componentType' => Form\Field::NAME,
            'prefer' => 'toggle',
            'valueMap' => [
                'true' => '1',
                'false' => '2'
            ],
        ];

        $path = $this->arrayManager->findPath(ProductAttributeInterface::CODE_STATUS, $meta, null, 'children');
        $meta = $this->arrayManager->merge($path . static::META_CONFIG_PATH, $meta, $switcherConfig);

        return $meta;
    }
}
