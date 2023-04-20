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

namespace Smile\Seller\Setup;

use Magento\Eav\Setup\EavSetup;
use Smile\Seller\Api\Data\SellerInterface;
use Smile\Retailer\Api\Data\RetailerInterface;

/**
 * Seller Setup class : contains EAV Attributes declarations.
 *
 * @category Smile
 * @package  Smile\Seller
 * @author   Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
class SellerSetup extends EavSetup
{

    /**
     * Default entities and attributes
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getDefaultEntities(): array
    {
        return [
            SellerInterface::ENTITY => [
                'entity_model' => 'Smile\Seller\Model\ResourceModel\Seller',
                'attribute_model' => 'Smile\Seller\Model\ResourceModel\Seller\Attribute',
                'table' => 'smile_seller_entity',
                'entity_attribute_collection' => 'Smile\Seller\Model\ResourceModel\Seller\Attribute\Collection',
                'attributes' => [
                    'name' => [
                        'type' => 'varchar',
                        'label' => 'Name',
                        'input' => 'text',
                        'sort_order' => 1,
                    ],
                    'created_at' => [
                        'type' => 'static',
                        'input' => 'date',
                        'sort_order' => 19,
                        'visible' => false,
                    ],
                    'updated_at' => [
                        'type' => 'static',
                        'input' => 'date',
                        'sort_order' => 20,
                    ],
                    'seller_code' => [
                        'type' => 'static',
                        'label' => 'Seller Code',
                        'input' => 'text',
                        'frontend_class' => 'validate-length maximum-length-64',
                        'unique' => true,
                    ],
                    'is_active' => [
                        'type' => 'int',
                        'label' => 'Is Active',
                        'input' => 'select',
                        'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                        'sort_order' => 2,
                    ],
                    'description' => [
                        'type' => 'text',
                        'label' => 'Description',
                        'input' => 'textarea',
                        'required' => false,
                        'sort_order' => 4,
                    ],
                    'meta_title' => [
                        'type' => 'varchar',
                        'label' => 'Page Title',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 6,
                    ],
                    'meta_keywords' => [
                        'type' => 'text',
                        'label' => 'Meta Keywords',
                        'input' => 'textarea',
                        'required' => false,
                        'sort_order' => 7,
                    ],
                    'meta_description' => [
                        'type' => 'text',
                        'label' => 'Meta Description',
                        'input' => 'textarea',
                        'required' => false,
                        'sort_order' => 8,
                    ],
                ],
            ],
        ];
    }
}
