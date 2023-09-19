<?php

declare(strict_types=1);

namespace Smile\Seller\Setup\Patch;

use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Smile\Seller\Api\Data\SellerInterface;
use Smile\Seller\Model\ResourceModel\Seller;
use Smile\Seller\Model\ResourceModel\Seller\Attribute;
use Smile\Seller\Model\ResourceModel\Seller\Attribute\Collection as AttributeCollection;

/**
 * Seller Setup class : contains EAV Attributes declarations.
 */
class SellerSetup extends EavSetup
{
    /**
     * @inheritdoc
     */
    public function getDefaultEntities(): array
    {
        return [
            SellerInterface::ENTITY_TYPE_CODE => [
                'entity_model' => Seller::class,
                'attribute_model' => Attribute::class,
                'table' => 'smile_seller_entity',
                'entity_attribute_collection' => AttributeCollection::class,
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
                        'source' => Boolean::class,
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
