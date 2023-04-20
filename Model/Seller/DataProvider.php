<?php

/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\Seller
 * @author    Ihor KVASNYTSKYI <ihor.kvasnytskyi@smile-ukraine.com>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\Seller\Model\Seller;

use Magento\Catalog\Model\Category\DataProvider as CatalogCategoryDataProvider;

class DataProvider extends CatalogCategoryDataProvider
{
    /**
     * @return array
     */
    protected function getFieldsMap(): array
    {
        $fields = parent::getFieldsMap();
        $fields['content'][] = 'image';

        return $fields;
    }
}
