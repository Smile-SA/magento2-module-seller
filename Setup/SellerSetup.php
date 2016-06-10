<?php
/**
 * Catalog entity setup
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Smile\Seller\Setup;

use Magento\Eav\Setup\EavSetup;
use Smile\Seller\Api\Data\SellerInterface;

class SellerSetup extends EavSetup
{

    /**
     * Default entities and attributes
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getDefaultEntities()
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
                    'sku' => [
                        'type' => 'static',
                        'label' => 'SKU',
                        'input' => 'text',
                        'frontend_class' => 'validate-length maximum-length-64',
                        //@todo 'backend' => 'Magento\Catalog\Model\Product\Attribute\Backend\Sku',
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
                    'image' => [
                        'type' => 'varchar',
                        'label' => 'Image',
                        'input' => 'image',
                        //'backend' => 'Magento\Catalog\Model\Category\Attribute\Backend\Image',
                        'required' => false,
                        'sort_order' => 5,
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
                        'sort_order' => 7
                    ],
                    'meta_description' => [
                        'type' => 'text',
                        'label' => 'Meta Description',
                        'input' => 'textarea',
                        'required' => false,
                        'sort_order' => 8,
                    ]
                ],
            ],
        ];
    }
}
