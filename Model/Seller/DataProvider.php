<?php
namespace Smile\Seller\Model\Seller;

class DataProvider extends \Magento\Catalog\Model\Category\DataProvider
{

    protected function getFieldsMap()
    {
        $fields = parent::getFieldsMap();
        $fields['content'][] = 'image'; // custom image field

        return $fields;
    }
}