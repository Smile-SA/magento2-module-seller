<?php

declare(strict_types=1);

namespace Smile\Seller\Model\Seller;

use Magento\Catalog\Model\Category\DataProvider as CatalogCategoryDataProvider;

class DataProvider extends CatalogCategoryDataProvider
{
    /**
     * @inheritdoc
     */
    protected function getFieldsMap()
    {
        $fields = parent::getFieldsMap();
        $fields['content'][] = 'image';

        return $fields;
    }
}
